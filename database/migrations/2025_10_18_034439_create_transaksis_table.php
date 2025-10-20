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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_member');
            $table->uuid('uuid_paket');
            $table->string('tipe_member');
            $table->string('no_invoice');
            $table->string('jenis_pembayaran');
            $table->string('total_bayar');
            $table->string('tanggal_mulai');
            $table->string('tanggal_selesai')->nullable();
            $table->string('remaining_session')->nullable();
            $table->string('status');
            $table->string('keterangan')->nullable();
            $table->string('bukti')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
