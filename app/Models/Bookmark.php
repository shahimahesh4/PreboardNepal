<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $guarded = ['id'];

    public function document()
    {
        return $this->belongsTo(StudyDocument::class, 'study_document_id');
    }
}
