<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    // Ambil semua user yang role-nya 'dosen'
    public function index()
    {
        $dosen = User::where('role', 'dosen')->get(['id', 'name']);
        return response()->json(['data' => $dosen]);
    }
}