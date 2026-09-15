<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentReport extends Model
{
    protected $guarded = ['id'];

    public function document()
    {
        return $this->belongsTo(StudyDocument::class, 'study_document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
