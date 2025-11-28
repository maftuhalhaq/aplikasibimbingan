<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// --- BAGIAN INI SERING DILUPAKAN ---
use App\Models\User;                 // Agar bisa panggil tabel User
use Illuminate\Support\Facades\Hash; // Agar bisa cek password
use Illuminate\Support\Facades\Auth; // Agar fungsi Auth jalan
use Illuminate\Validation\ValidationException;
// -----------------------------------

class AuthController extends Controller
{
    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Login API
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // 1. Validasi Input termasuk FCM token
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'fcm_token' => 'nullable|string', // Tambahan
        ]);

        // 2. Cari User berdasarkan Email
        $user = User::where('email', $request->email)->first();

        // 3. Cek apakah User ada DAN Password benar
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau Password salah',
            ], 401);
        }

        // 4. Hapus token lama (opsional, biar bersih)
        $user->tokens()->delete();

        // 5. SIMPAN TOKEN FCM (Tambahan)
        if ($request->fcm_token) {
            $user->update([
                'fcm_token' => $request->fcm_token
            ]);
        }

        // 6. Buat Token Baru
        $token = $user->createToken('auth_token')->plainTextToken;

        // 7. Kirim Respon Sukses
        return response()->json([
            'message' => 'Login Sukses',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => $user
        ]);
    }

    // Fungsi Ganti Password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // 'confirmed' butuh field 'new_password_confirmation'
        ]);

        $user = $request->user();

        // 1. Cek Password Lama
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah!'], 400);
        }

        // 2. Update Password Baru
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Password berhasil diganti']);
    }
}