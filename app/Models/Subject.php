<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function documents()
    {
        return $this->hasManyThrough(StudyDocument::class, Chapter::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
