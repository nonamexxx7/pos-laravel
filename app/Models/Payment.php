<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['sale_id', 'method', 'amount', 'received', 'change', 'reference'];

    public const METHODS = ['cash' => 'เงินสด', 'transfer' => 'โอน/พร้อมเพย์', 'card' => 'บัตรเครดิต/เดบิต'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
