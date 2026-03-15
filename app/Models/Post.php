<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
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
