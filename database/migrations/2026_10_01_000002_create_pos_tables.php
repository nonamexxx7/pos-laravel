<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();
            $t->string('name');
            $t->string('phone', 20)->unique();
            $t->string('email')->nullable();
            $t->unsignedInteger('points')->default(0);
            $t->decimal('total_spent', 12, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('sku', 40)->unique();
            $t->string('name');
            $t->decimal('price', 10, 2);
            $t->decimal('cost', 10, 2)->default(0);
            $t->integer('stock')->default(0);
            $t->unsignedInteger('min_stock')->default(5);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 20); // in | out | adjust | sale | void
            $t->integer('qty');     // บวก = เข้า, ลบ = ออก
            $t->integer('balance'); // คงเหลือหลังทำรายการ
            $t->string('note')->nullable();
            $t->timestamps();
        });

        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_no', 30)->unique();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('subtotal', 12, 2);
            $t->decimal('discount', 12, 2)->default(0);
            $t->decimal('total', 12, 2);
            $t->unsignedInteger('points_earned')->default(0);
            $t->unsignedInteger('points_used')->default(0);
            $t->string('status', 20)->default('completed'); // completed | void
            $t->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->decimal('price', 10, 2);
            $t->decimal('cost', 10, 2)->default(0);
            $t->unsignedInteger('qty');
            $t->decimal('total', 12, 2);
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $t->string('method', 20); // cash | transfer | card
            $t->decimal('amount', 12, 2);
            $t->decimal('received', 12, 2);
            $t->decimal('change', 12, 2)->default(0);
            $t->string('reference')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['payments', 'sale_items', 'sales', 'stock_movements', 'products', 'categories', 'members'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
