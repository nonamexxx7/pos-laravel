<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'email', 'points', 'total_spent'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getLevelAttribute(): string
    {
        return match (true) {
            $this->total_spent >= 20000 => 'Gold',
            $this->total_spent >= 5000 => 'Silver',
            default => 'Standard',
        };
    }

    public static function nextCode(): string
    {
        return 'M'.str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }
}
