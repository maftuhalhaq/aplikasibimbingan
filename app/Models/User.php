<?php

namespace App\Models;

// 1. Tambahkan Library Sanctum
use Laravel\Sanctum\HasApiTokens; // <--- WAJIB ADA
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // 2. Pasang Trait-nya di sini
    use HasApiTokens, HasFactory, Notifiable; // <--- WAJIB ADA 'HasApiTokens'

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nomor_induk',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}