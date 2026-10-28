<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function cashier(): User
    {
        return User::factory()->create(['role' => 'cashier']);
    }

    private function product(int $stock = 10, float $price = 250): Product
    {
        $product = Product::create(['sku' => 'T'.uniqid(), 'name' => 'สินค้าทดสอบ', 'price' => $price, 'cost' => 100, 'stock' => 0]);
        $product->moveStock($stock, 'in', null, 'เริ่มต้น');

        return $product->fresh();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_can_login_and_inactive_user_cannot(): void
    {
        $user = User::factory()->create(['email' => 'a@pos.test', 'password' => 'secret12']);
        $this->post('/login', ['email' => 'a@pos.test', 'password' => 'secret12'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        auth()->logout();
        $user->update(['is_active' => false]);
        $this->post('/login', ['email' => 'a@pos.test', 'password' => 'secret12'])->assertSessionHasErrors('email');
    }

    public function test_cashier_cannot_access_admin_pages(): void
    {
        $this->actingAs($this->cashier());
        $this->get('/employees')->assertForbidden();
        $this->get('/reports')->assertForbidden();
        $this->get('/products/create')->assertForbidden();
    }

    public function test_cash_sale_reduces_stock_and_calculates_change(): void
    {
        $product = $this->product(10, 250);
        $this->actingAs($this->cashier());

        $response = $this->postJson('/pos', [
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'method' => 'cash',
            'received' => 600,
        ])->assertOk();

        $sale = Sale::with('payment')->firstOrFail();
        $this->assertEquals(500, $sale->total);
        $this->assertEquals(100, $sale->payment->change);
        $this->assertSame(8, $product->fresh()->stock);
        $this->get($response->json('receipt'))->assertOk();
    }

    public function test_cannot_sell_more_than_stock_or_pay_less_than_total(): void
    {
        $product = $this->product(1, 100);
        $this->actingAs($this->cashier());

        $this->postJson('/pos', ['items' => [['product_id' => $product->id, 'qty' => 2]], 'method' => 'cash', 'received' => 500])->assertStatus(422);
        $this->postJson('/pos', ['items' => [['product_id' => $product->id, 'qty' => 1]], 'method' => 'cash', 'received' => 50])->assertStatus(422);

        $this->assertSame(0, Sale::count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_member_earns_and_redeems_points(): void
    {
        $member = Member::create(['code' => 'M00001', 'name' => 'ทดสอบ', 'phone' => '0812345678', 'points' => 50]);
        $product = $this->product(10, 300);
        $this->actingAs($this->cashier());

        $this->postJson('/pos', [
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'member_id' => $member->id, 'use_points' => 30, 'method' => 'transfer',
        ])->assertOk();

        $sale = Sale::firstOrFail();
        $this->assertEquals(270, $sale->total);
        $this->assertSame(2, $sale->points_earned);
        $this->assertSame(22, $member->fresh()->points); // 50 - 30 + 2
    }

    public function test_admin_can_void_sale_and_stock_and_points_are_restored(): void
    {
        $member = Member::create(['code' => 'M00001', 'name' => 'ทดสอบ', 'phone' => '0812345678']);
        $product = $this->product(10, 500);
        $this->actingAs($this->admin());

        $this->postJson('/pos', ['items' => [['product_id' => $product->id, 'qty' => 3]], 'member_id' => $member->id, 'method' => 'card'])->assertOk();
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame(15, $member->fresh()->points);

        $sale = Sale::firstOrFail();
        $this->post("/sales/{$sale->id}/void")->assertRedirect();

        $this->assertSame('void', $sale->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(0, $member->fresh()->points);
    }

    public function test_admin_can_adjust_stock_but_not_below_zero(): void
    {
        $product = $this->product(5);
        $this->actingAs($this->admin());

        $this->post("/products/{$product->id}/stock", ['type' => 'in', 'qty' => 5])->assertSessionHas('success');
        $this->assertSame(10, $product->fresh()->stock);

        $this->post("/products/{$product->id}/stock", ['type' => 'out', 'qty' => 99])->assertSessionHas('error');
        $this->assertSame(10, $product->fresh()->stock);

        $this->post("/products/{$product->id}/stock", ['type' => 'adjust', 'qty' => 7]);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_all_main_pages_render_for_admin(): void
    {
        $this->actingAs($this->admin());
        $product = $this->product();
        $member = Member::create(['code' => 'M00001', 'name' => 'ทดสอบ', 'phone' => '0812345678']);
        $this->postJson('/pos', ['items' => [['product_id' => $product->id, 'qty' => 1]], 'member_id' => $member->id, 'method' => 'cash', 'received' => 300]);
        $sale = Sale::firstOrFail();

        foreach (['/', '/pos', '/sales', "/sales/{$sale->id}", '/members', '/members/create', "/members/{$member->id}", "/members/{$member->id}/edit",
            '/products', '/products/create', "/products/{$product->id}/edit", '/stock-movements', '/categories',
            '/employees', '/employees/create', '/reports', '/profile'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/reports/export')->assertOk()->assertDownload();
    }
}
