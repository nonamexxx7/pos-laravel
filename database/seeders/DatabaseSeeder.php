<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Member;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'ผู้ดูแลระบบ', 'email' => 'admin@pos.test', 'password' => 'password', 'role' => 'admin', 'position' => 'ผู้จัดการร้าน', 'phone' => '0800000001']);
        User::create(['name' => 'สมหญิง แคชเชียร์', 'email' => 'cashier@pos.test', 'password' => 'password', 'role' => 'cashier', 'position' => 'พนักงานขาย', 'phone' => '0800000002']);

        $data = [
            'เครื่องดื่ม' => [['DR001', 'น้ำดื่ม 600ml', 7, 4, 120], ['DR002', 'โค้ก 325ml', 15, 10, 80], ['DR003', 'ชาเขียวเย็น', 20, 11, 50], ['DR004', 'กาแฟกระป๋อง', 18, 12, 4]],
            'ขนม' => [['SN001', 'มันฝรั่งทอดกรอบ', 20, 13, 60], ['SN002', 'ช็อกโกแลตแท่ง', 25, 16, 40], ['SN003', 'ขนมปังไส้ครีม', 12, 7, 3]],
            'ของใช้' => [['HH001', 'สบู่ก้อน', 22, 14, 35], ['HH002', 'แชมพู 170ml', 59, 40, 25], ['HH003', 'ยาสีฟัน', 45, 30, 30]],
            'อาหารสำเร็จรูป' => [['FD001', 'บะหมี่กึ่งสำเร็จรูป', 8, 5, 200], ['FD002', 'ข้าวกล่องไก่ทอด', 45, 30, 15]],
        ];
        foreach ($data as $cat => $items) {
            $c = Category::create(['name' => $cat]);
            foreach ($items as [$sku, $name, $price, $cost, $stock]) {
                $p = $c->products()->create(['sku' => $sku, 'name' => $name, 'price' => $price, 'cost' => $cost, 'stock' => 0, 'min_stock' => 5]);
                $p->moveStock($stock >= 15 ? $stock * 3 : $stock, 'in', 1,'สต็อกเริ่มต้น');
            }
        }

        foreach ([['สมชาย ใจดี', '0811111111'], ['สุดา รักเรียน', '0822222222'], ['วิชัย มั่งมี', '0833333333']] as [$n, $ph]) {
            Member::create(['code' => Member::nextCode(), 'name' => $n, 'phone' => $ph]);
        }

        $this->seedDemoSales();
    }

    /**
     * สร้างบิลตัวอย่างย้อนหลัง 14 วัน เพื่อให้แดชบอร์ดและรายงานมีข้อมูลแสดง
     */
    private function seedDemoSales(): void
    {
        mt_srand(42);
        $products = Product::all();
        $users = User::pluck('id');
        $members = Member::pluck('id');

        for ($day = 13; $day >= 0; $day--) {
            for ($n = mt_rand(3, 7); $n > 0; $n--) {
                $at = now()->subDays($day)->setTime(mt_rand(9, 20), mt_rand(0, 59));
                $picked = $products->shuffle()->take(mt_rand(1, 4));
                $subtotal = 0;
                $lines = [];
                foreach ($picked as $p) {
                    $qty = mt_rand(1, 3);
                    if ($p->fresh()->stock - $qty < 3) {
                        continue; // ไม่ให้สต็อกตัวอย่างติดลบ
                    }
                    $lines[] = [$p, $qty];
                    $subtotal += $p->price * $qty;
                }
                if (! $lines) {
                    continue;
                }
                $memberId = mt_rand(0, 2) === 0 ? $members->random() : null;
                $sale = Sale::create([
                    'invoice_no' => 'INV'.$at->format('Ymd').'-'.str_pad((string) (Sale::whereDate('created_at', $at)->count() + 1), 4, '0', STR_PAD_LEFT),
                    'user_id' => $users->random(),
                    'member_id' => $memberId,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'points_earned' => $memberId ? intdiv((int) $subtotal, 100) : 0,
                ]);
                foreach ($lines as [$p, $qty]) {
                    $sale->items()->create(['product_id' => $p->id, 'name' => $p->name, 'price' => $p->price, 'cost' => $p->cost, 'qty' => $qty, 'total' => $p->price * $qty]);
                    $p->moveStock(-$qty, 'sale', $sale->user_id, $sale->invoice_no);
                }
                $method = ['cash', 'cash', 'transfer', 'card'][mt_rand(0, 3)];
                $received = $method === 'cash' ? ceil($subtotal / 20) * 20 : $subtotal;
                $sale->payment()->create(['method' => $method, 'amount' => $subtotal, 'received' => $received, 'change' => $received - $subtotal]);
                if ($memberId) {
                    Member::whereKey($memberId)->update(['points' => DB::raw('points + '.$sale->points_earned), 'total_spent' => DB::raw('total_spent + '.$subtotal)]);
                }
                $sale->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
            }
        }
    }
}
