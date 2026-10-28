<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'sku', 'name', 'price', 'cost', 'stock', 'min_stock', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function isLow(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /** ปรับสต็อกและบันทึกประวัติ (qty ติดลบ = ตัดออก) */
    public function moveStock(int $qty, string $type, ?int $userId, ?string $note = null): void
    {
        $this->increment('stock', $qty);
        $this->movements()->create([
            'user_id' => $userId,
            'type' => $type,
            'qty' => $qty,
            'balance' => $this->fresh()->stock,
            'note' => $note,
        ]);
    }
}
