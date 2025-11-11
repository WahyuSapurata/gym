<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Clas extends Model
{
    use HasFactory;

    protected $table = 'clas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_instruktur',
        'nama_clas',
        'harga',
        'kategori',
        'jadwal',
        'durasi',
        'slot',
        'banner',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function instruktur()
    {
        return $this->belongsTo(Instruktur::class, 'uuid_instruktur', 'uuid');
    }
}
