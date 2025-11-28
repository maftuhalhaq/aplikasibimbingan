<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'receiver_id', 'message'];

    // Relasi ke pengirim (untuk tau nama pengirim)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}