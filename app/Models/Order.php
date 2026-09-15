<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $attributes = ['provider' => 'khalti'];

    public function providerLabel(): string
    {
        return match ($this->provider) {
            'esewa' => 'eSewa', 'khalti' => 'Khalti', default => 'Payment provider'
        };
    }

    protected $hidden = ['payment_url'];

    protected function casts(): array
    {
        return ['amount_paisa' => 'integer', 'duration_days' => 'integer', 'verified_at' => 'datetime', 'last_checked_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
