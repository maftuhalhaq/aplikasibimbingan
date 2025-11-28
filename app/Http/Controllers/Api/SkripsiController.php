<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Skripsi;
use Illuminate\Support\Facades\Log;

class SkripsiController extends Controller
{
    // PASTIKAN NAMA FUNGSI INI 'store' (huruf kecil semua)
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'deskripsi' => 'required',
            'dosen_id' => 'required|exists:users,id' // Wajib pilih dosen valid
        ]);

        $skripsi = \App\Models\Skripsi::create([
            'user_id' => $request->user()->id,
            'dosen_id' => $request->dosen_id, // <--- Simpan pilihan mahasiswa
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'status' => 'Pending'
        ]);

        return response()->json([
            'message' => 'Judul berhasil diajukan ke Dosen!',
            'data' => $skripsi
        ]);
    }

    public function index(Request $request)
    {
        $skripsi = \App\Models\Skripsi::where('user_id', $request->user()->id)
            ->latest()
            ->first(); // <--- Ambil satu saja yang terbaru

        return response()->json(['data' => $skripsi]);
    }
}