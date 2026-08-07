<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'category_id',
        'section',
        'auth_id',
        'auth_type',
        'image_url',
        'image_urls',
        'release_at',
        'attachment_urls',
    ];
}
