<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Archive extends Model
{
    protected $fillable = [
        'name',
        'code',
        'folder_id',
        'user_id',
        'category_id',
    ];

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
