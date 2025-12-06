<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ReferalPoint extends Model
{
    use HasFactory;

    protected $table = 'referal_points';
    protected $primaryKey = 'id';
    protected $fillable = [
        'uuid',
        'uuid_member',
        'point',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event listener untuk membuat UUID sebelum menyimpan
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'uuid_member', 'uuid');
    }
}
