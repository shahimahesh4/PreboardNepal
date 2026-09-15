<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    protected $attributes = ['status' => 'active', 'revision' => 0];

    protected $guarded = ['id'];

    protected $hidden = ['snapshot'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'answers' => 'array', 'started_at' => 'datetime', 'deadline_at' => 'datetime', 'saved_at' => 'datetime', 'submitted_at' => 'datetime'];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
