<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bimbingan;
use Illuminate\Support\Facades\Log;

class BimbinganController extends Controller
{
    // Fungsi Upload (Mahasiswa)
    // 1. FUNGSI UPLOAD (Store)
    public function store(Request $request)
    {
        $request->validate([
            'catatan' => 'required',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // PERBAIKAN DISINI: Tambahkan ->latest()
        // Agar mengambil skripsi paling baru (ID 11), bukan yang lama (ID 1)
        $skripsi = \App\Models\Skripsi::where('user_id', $request->user()->id)
            ->latest() // <--- WAJIB ADA
            ->first();

        if (!$skripsi) {
            return response()->json(['message' => 'Judul tidak ditemukan'], 404);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('files', 'public');
        }

        $bimbingan = Bimbingan::create([
            'skripsi_id' => $skripsi->id,
            'catatan' => $request->catatan,
            'file_path' => $path,
            'status' => 'Revisi'
        ]);

        return response()->json(['message' => 'Berhasil', 'data' => $bimbingan]);
    }

    // 2. FUNGSI LIST (Index - Mahasiswa)
    public function index(Request $request)
    {
        // PERBAIKAN DISINI JUGA
        $skripsi = \App\Models\Skripsi::where('user_id', $request->user()->id)
            ->latest() // <--- WAJIB ADA
            ->first();

        if (!$skripsi) {
            return response()->json(['data' => []]);
        }

        $bimbingan = Bimbingan::where('skripsi_id', $skripsi->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $bimbingan]);
    }

    // Fungsi Lihat List (Dosen)
    public function indexDosen(Request $request)
    {
        // 1. Cek ID Dosen yang sedang login
        $idDosen = $request->user()->id;
        \Illuminate\Support\Facades\Log::info("--- DEBUG DOSEN ---");
        \Illuminate\Support\Facades\Log::info("Dosen yang Login ID: " . $idDosen);

        // 2. Cek Query Database
        $data = Bimbingan::whereHas('skripsi', function ($query) use ($idDosen) {
            $query->where('dosen_id', $idDosen);
        })
            ->with('skripsi.user')
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Cek Berapa Data yang Ditemukan
        \Illuminate\Support\Facades\Log::info("Jumlah Bimbingan Ditemukan: " . $data->count());

        if ($data->count() > 0) {
            // Cek detail data pertama biar yakin
            \Illuminate\Support\Facades\Log::info("Contoh Data ID: " . $data[0]->id);
            \Illuminate\Support\Facades\Log::info("Milik Mahasiswa: " . $data[0]->skripsi->user->name);
        } else {
            \Illuminate\Support\Facades\Log::info("DATA KOSONG! Cek apakah tabel bimbingan punya skripsi_id yang mengarah ke dosen_id ini.");
        }

        // ... (Lanjutan mapping data seperti biasa) ...
        $result = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'student_id' => $item->skripsi->user_id,
                'judul' => $item->skripsi ? $item->skripsi->judul : 'Judul dihapus',
                'nama_mahasiswa' => ($item->skripsi && $item->skripsi->user) ? $item->skripsi->user->name : 'Mhs Terhapus',
                'catatan' => $item->catatan,
                'status' => $item->status,
                'created_at' => $item->created_at->format('Y-m-d H:i'),
                'file_path' => $item->file_path
            ];
        });

        return response()->json(['data' => $result]);
    }

    // Fungsi Update Status (Dosen) - BAGIAN KRUSIAL
    public function update(Request $request, $id)
    {
        $bimbingan = Bimbingan::with('skripsi.user')->find($id);

        if (!$bimbingan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // 1. UPDATE DATABASE (Sukses disini)
        $bimbingan->update(['status' => $request->status]);

        // 2. KIRIM NOTIFIKASI (Dengan Pengaman)
        try {
            // Cek kelengkapan data sebelum kirim
            if ($bimbingan->skripsi && $bimbingan->skripsi->user) {
                $user = $bimbingan->skripsi->user;

                // Cek apakah user punya token FCM & fungsi curl ada
                if ($user->fcm_token && function_exists('curl_init')) {
                    $title = "Status Skripsi Baru!";
                    $body = "Status bimbingan Anda kini: " . $request->status;

                    $this->sendFCM($user->fcm_token, $title, $body);
                }
            }
        } catch (\Throwable $e) {
            // JIKA ERROR, DIAM SAJA (Cuma catat di log server)
            // Jangan biarkan error ini membuat respon ke Android jadi Gagal
            Log::error("Gagal kirim notif FCM: " . $e->getMessage());
        }

        // 3. KIRIM RESPON SUKSES KE ANDROID
        return response()->json(['message' => 'Status berhasil diupdate']);
    }

    // Fungsi Helper FCM (Private)
    private function sendFCM($token, $title, $body)
    {
        // GANTI DENGAN SERVER KEY FIREBASE KAMU YANG ASLI
        $serverKey = 'AAAA_GANTI_DENGAN_KEY_PANJANG_DARI_FIREBASE_CONSOLE';

        $url = "https://fcm.googleapis.com/fcm/send";
        $data = [
            "to" => $token,
            "notification" => [
                "title" => $title,
                "body" => $body,
                "sound" => "default"
            ]
        ];

        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL (Penting buat Localhost)
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}