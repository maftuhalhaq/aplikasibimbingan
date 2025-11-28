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
        Schema::create('bimbingans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skripsi_id')->constrained('skripsis')->onDelete('cascade');
            $table->text('catatan');
            $table->string('file_path')->nullable();

            // --- TAMBAHKAN BARIS INI ---
            $table->string('status')->default('Revisi');
            // ---------------------------

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimbingans');
    }
};