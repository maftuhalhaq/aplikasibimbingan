<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bimbingan;
use App\Models\Skripsi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BimbinganController extends Controller
{
    // =================================================================
    // 1. FUNGSI UPLOAD (MAHASISWA)
    // =================================================================
    public function store(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'skripsi_id' => 'required',
            'catatan' => 'required',
            'file_dokumen' => 'required|mimes:pdf|max:5120', // Max 5MB biar aman
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'data' => $validator->errors()
            ], 400);
        }

        // Simpan File
        if ($request->hasFile('file_dokumen')) {
            $path = $request->file('file_dokumen')->store('bimbingan', 'public');

            $bimbingan = Bimbingan::create([
                'skripsi_id' => $request->skripsi_id,
                'catatan' => $request->catatan,
                'status' => 'pending', // Default status
                'file_path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bimbingan berhasil dikirim',
                'data' => $bimbingan
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'File tidak ditemukan / Gagal Upload'
        ], 400);
    }

    // =================================================================
    // 2. FUNGSI LIST (MAHASISWA) - PERBAIKAN UTAMA DISINI
    // =================================================================
    public function index(Request $request)
    {
        // A. TANGKAP ID DARI ANDROID
        $skripsi_id = $request->query('skripsi_id');

        // B. LOGIKA PENCARIAN
        if ($skripsi_id) {
            // Jika Android mengirim ID, pakai ID itu (LEBIH AKURAT)
            $data = Bimbingan::where('skripsi_id', $skripsi_id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // FALLBACK: Jika Android lupa kirim ID, cari manual via User
            $skripsi = Skripsi::where('user_id', $request->user()->id)
                ->latest()
                ->first();

            if ($skripsi) {
                $data = Bimbingan::where('skripsi_id', $skripsi->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $data = collect([]); // Kosong
            }
        }

        // C. RETURN JSON LENGKAP (SESUAI MODEL ANDROID)
        // Android butuh: success, message, data
        return response()->json([
            'success' => true,
            'message' => 'List Data Bimbingan',
            'data' => $data
        ], 200);
    }

    // =================================================================
    // 3. FUNGSI LIST (DOSEN)
    // =================================================================
    public function indexDosen(Request $request)
    {
        $idDosen = $request->user()->id;

        // Query ambil semua bimbingan milik mahasiswa bimbingan dosen ini
        $data = Bimbingan::whereHas('skripsi', function ($query) use ($idDosen) {
            $query->where('dosen_id', $idDosen);
        })
            ->with('skripsi.user') // Load relasi user untuk dapat nama mahasiswa
            ->orderBy('created_at', 'desc')
            ->get();

        // Mapping Data agar rapi di Android
        $result = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'student_id' => $item->skripsi->user_id ?? 0,
                'judul' => $item->skripsi->judul ?? 'Judul Tidak Ada',
                'nama_mahasiswa' => $item->skripsi->user->name ?? 'Mahasiswa Dihapus',
                'catatan' => $item->catatan,
                'status' => $item->status,
                'created_at' => $item->created_at->format('Y-m-d H:i'),
                'file_path' => $item->file_path
            ];
        });

        // Format JSON Dosen juga harus lengkap
        return response()->json([
            'success' => true,
            'message' => 'List Bimbingan Mahasiswa',
            'data' => $result
        ], 200);
    }

    // =================================================================
    // 4. FUNGSI UPDATE STATUS (DOSEN)
    // =================================================================
    public function update(Request $request, $id)
    {
        $bimbingan = Bimbingan::with('skripsi.user')->find($id);

        if (!$bimbingan) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // Update Database
        $bimbingan->update(['status' => $request->status]);

        // Kirim Notifikasi FCM (Opsional, dibungkus Try-Catch agar tidak bikin error)
        try {
            if ($bimbingan->skripsi && $bimbingan->skripsi->user) {
                $user = $bimbingan->skripsi->user;
                if ($user->fcm_token) {
                    $this->sendFCM(
                        $user->fcm_token,
                        "Status Bimbingan Diupdate",
                        "Dosen mengubah status menjadi: " . $request->status
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error("FCM Error: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diubah menjadi ' . $request->status
        ]);
    }

    // =================================================================
    // HELPER: KIRIM NOTIFIKASI
    // =================================================================
    private function sendFCM($token, $title, $body)
    {
        // Masukkan Server Key Firebase Anda disini
        $serverKey = 'GANTI_INI_DENGAN_SERVER_KEY_FIREBASE_ANDA';

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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // =================================================================
    // 5. FUNGSI HAPUS BIMBINGAN (MAHASISWA)
    // =================================================================
    public function destroy(Request $request, $id)
    {
        $bimbingan = Bimbingan::find($id);

        if (!$bimbingan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Opsional: Cek apakah status masih pending?
        // Biasanya mahasiswa tidak boleh menghapus data yang sudah di-ACC dosen.
        if ($bimbingan->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus bimbingan yang sudah diperiksa dosen!'
            ], 403);
        }

        // 1. Hapus File Fisik di Storage
        if ($bimbingan->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($bimbingan->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($bimbingan->file_path);
        }

        // 2. Hapus Data di Database
        $bimbingan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data bimbingan berhasil dihapus'
        ]);
    }
}