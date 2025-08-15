<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
class Archive extends Model
{
    protected $fillable = [
        'name',
        'code',
        'folder_id',
        'user_id',
        'category_id',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Agar kod hali berilmagan bo‘lsa
            if (empty($model->code)) {
                do {
                    $code = strtoupper(Str::random(6)); // Masalan: 8 ta belgili
                } while (self::where('code', $code)->exists());

                $model->code = $code;
            }
        });
    }
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function files(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachment');
    }
}
