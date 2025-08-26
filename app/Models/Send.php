<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Send extends Model
{
    protected $fillable = ['url', 'user_id', 'folder_id', 'users'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
