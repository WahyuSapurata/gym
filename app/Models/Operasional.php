<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Operasional extends Model
{
    use HasFactory;

    protected $table = 'operasionals';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'deskripsi',
        'biaya_operasional',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}
