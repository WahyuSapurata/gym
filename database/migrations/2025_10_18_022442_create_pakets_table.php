<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pakets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('tipe_member');
            $table->string('nama_paket');
            $table->string('durasi_hari')->nullable();
            $table->string('total_sesi')->nullable();
            $table->string('harga');
            $table->string('deskripsi')->nullable();
            $table->string('status');
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pakets');
    }
};
