<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Url;

class Attachment extends Model
{
    protected $fillable = [
        'extra_identifier',
        'title',
        'path',
        'size',
        'type',
        'attachment_type',
        'attachment_id',
    ];
    public function url(): Attribute
    {
        return Attribute::make(
            get: fn () => Url::to('storage/' . $this->path),
        );
    }
    public function attachment(): MorphTo
    {
        return $this->morphTo();
    }
}
