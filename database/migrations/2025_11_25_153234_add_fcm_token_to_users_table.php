<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations. (MENAMBAHKAN KOLOM)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Masukkan kode ini di sini (UP)
            $table->string('fcm_token')->nullable(); 
        });
    }

    /**
     * Reverse the migrations. (MENGHAPUS KOLOM)
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Masukkan kode hapus di sini (DOWN)
            $table->dropColumn('fcm_token');
        });
    }
};