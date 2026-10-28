<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['sale_id', 'product_id', 'name', 'price', 'cost', 'qty', 'total'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
