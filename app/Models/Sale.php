<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['invoice_no', 'user_id', 'member_id', 'subtotal', 'discount', 'total', 'points_earned', 'points_used', 'status'];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public static function nextInvoiceNo(): string
    {
        $prefix = 'INV'.now()->format('Ymd');
        $n = static::where('invoice_no', 'like', $prefix.'%')->count() + 1;

        return $prefix.'-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
