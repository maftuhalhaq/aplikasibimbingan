<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skripsi extends Model
{
    use HasFactory;

    // --- INI YANG SERING DILUPAKAN ---
    // Kita harus mendaftar kolom apa saja yang boleh diisi
    protected $fillable = [
        'user_id',
        'dosen_id',
        'judul',
        'deskripsi',
        'status',
    ];

    // Relasi ke User (Opsional tapi bagus ada)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}