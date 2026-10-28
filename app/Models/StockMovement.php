<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'user_id', 'type', 'qty', 'balance', 'note'];

    public const TYPES = ['in' => 'รับเข้า', 'out' => 'ตัดออก', 'adjust' => 'ปรับยอด', 'sale' => 'ขาย', 'void' => 'ยกเลิกบิล'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
