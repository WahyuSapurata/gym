<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class TransaksiClas extends Model
{
    use HasFactory;

    protected $table = 'transaksi_clas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_clas',
        'nama',
        'jenis_kelamin',
        'alamat',
        'nomor_telepon',
        'tanggal_lahir',
        'total_bayar',
        'bukti_pembayaran',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function clas()
    {
        return $this->belongsTo(Clas::class, 'uuid_clas', 'uuid');
    }
}
