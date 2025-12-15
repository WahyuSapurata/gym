<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_member',
        'uuid_paket',
        'tipe_member',
        'no_invoice',
        'jenis_pembayaran',
        'tanggal_pembayaran',
        'total_bayar',
        'tanggal_mulai',
        'tanggal_selesai',
        'remaining_session',
        'status',
        'is_active',
        'keterangan',
        'bukti',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    // Model Transaksi.php
    public function member()
    {
        return $this->belongsTo(Member::class, 'uuid_member', 'uuid');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'uuid_paket', 'uuid');
    }
}
