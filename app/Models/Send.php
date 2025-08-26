<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Send extends Model
{
    protected $fillable = [
        'url',
        'user_id',
        'type_id',
        'users'
    ];
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
