<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('skripsis', function (Blueprint $table) {
            // Menambahkan kolom dosen_id yang berelasi ke tabel users
            $table->foreignId('dosen_id')->nullable()->after('user_id')->constrained('users');
        });
    }

    public function down()
    {
        Schema::table('skripsis', function (Blueprint $table) {
            $table->dropColumn('dosen_id');
        });
    }
};