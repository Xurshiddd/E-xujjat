<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Folder extends Model
{
    protected $fillable = [
        "name",
        "code",
        "user_id"
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Agar kod hali berilmagan bo‘lsa
            if (empty($model->code)) {
                do {
                    $code = strtoupper(Str::random(4)); // Masalan: 8 ta belgili
                } while (self::where('code', $code)->exists());

                $model->code = $code;
            }
        });
    }
    public function archives()
    {
        return $this->hasMany(Archive::class);
    }
    public function shares() {
    return $this->morphMany(Share::class, 'shareable');
}
}
