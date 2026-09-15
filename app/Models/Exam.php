<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['questions'];

    protected function casts(): array
    {
        return ['questions' => 'array'];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
