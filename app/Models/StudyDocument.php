<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudyDocument extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['body'];

    protected function casts(): array
    {
        return ['is_free' => 'boolean', 'reviewed_at' => 'datetime'];
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')->whereHas('chapter.subject', fn ($q) => $q->where('is_active', true));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
