<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    use HasFactory;

    protected $fillable = [
        'skripsi_id',
        'catatan',
        'file_path',
        'status'
    ];

    // --- TAMBAHKAN FUNGSI RELASI INI ---
    public function skripsi()
    {
        return $this->belongsTo(Skripsi::class);
    }
}