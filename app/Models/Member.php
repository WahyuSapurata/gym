<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Member extends Model
{
    use HasFactory;

    protected $table = 'members';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_user',
        'member_id',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat',
        'expired_at',
        'berat_badan',
        'tinggi_badan',
        'tipe_member',
        'status_member',
        'tgl_registrasi',
        'nomor_telepon',
        'foto_member',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'uuid_member', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uuid_user', 'uuid');
    }
}
