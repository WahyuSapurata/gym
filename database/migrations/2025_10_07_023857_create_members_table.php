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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('uuid_user');
            $table->string('member_id')->nullable();
            $table->string('jenis_kelamin');
            $table->string('tanggal_lahir');
            $table->string('alamat');
            $table->string('expired_at')->nullable();
            $table->string('berat_badan')->nullable();
            $table->string('tinggi_badan')->nullable();
            $table->string('tipe_member')->nullable();
            $table->string('status_member')->nullable();
            $table->string('tgl_registrasi');
            $table->string('nomor_telepon')->nullable();
            $table->string('foto_member')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
