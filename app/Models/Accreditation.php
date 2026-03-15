<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accreditation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "parent_id",
        "type",
        "parent_idx",
        "idx",
        "title",
        "description",
        "main_element",
        "main_element_fulfilment",
        "sample",
        "content",
        "is_complete",
        "attachment_urls",
        "student_ids",
        "lecture_ids",
        "user_ids",
        "auth_type",
        "auth_id",
        "auth_name",
        "created_at",
        "updated_at",
        "deleted_at",
    ];
}
