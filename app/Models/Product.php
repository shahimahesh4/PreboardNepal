<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['amount_paisa' => 'integer', 'duration_days' => 'integer', 'is_active' => 'boolean'];
    }
}
