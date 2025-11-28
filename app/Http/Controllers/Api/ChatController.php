<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Skripsi;
use App\Models\User; // Tambahkan ini untuk User
use Illuminate\Support\Facades\DB; // Tambahkan ini

class ChatController extends Controller
{
    // 1. KIRIM PESAN
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'message' => 'required'
        ]);

        $chat = Chat::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);

        // Kirim Notifikasi FCM (Opsional, kode sama seperti sebelumnya)
        // ... logic FCM ...

        return response()->json(['message' => 'Terkirim', 'data' => $chat]);
    }

    // 2. AMBIL RIWAYAT CHAT (Antara Saya dan Dia)
    public function getMessages(Request $request, $opponent_id)
    {
        $myId = $request->user()->id;

        // Ambil chat di mana (Pengirim=Saya & Penerima=Dia) ATAU (Pengirim=Dia & Penerima=Saya)
        $chats = Chat::where(function ($q) use ($myId, $opponent_id) {
            $q->where('sender_id', $myId)
                ->where('receiver_id', $opponent_id);
        })
            ->orWhere(function ($q) use ($myId, $opponent_id) {
                $q->where('sender_id', $opponent_id)
                    ->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc') // Urutkan dari lama ke baru (seperti WA)
            ->get();

        return response()->json(['data' => $chats]);
    }

    // 3. GET DOSEN ID (Khusus Mahasiswa agar tau harus chat ke siapa)
    public function getMyDosen(Request $request)
    {
        // Ambil skripsi TERBARU yang DOSENNYA TIDAK KOSONG
        $skripsi = Skripsi::where('user_id', $request->user()->id)
            ->whereNotNull('dosen_id') // <--- Hanya ambil yang punya dosen
            ->latest() // <--- Ambil yang paling baru (ID 11)
            ->first();

        if ($skripsi && $skripsi->dosen_id) {
            $dosen = User::find($skripsi->dosen_id);
            return response()->json(['data' => $dosen]);
        }

        return response()->json(['message' => 'Belum punya dosen'], 404);
    }
}