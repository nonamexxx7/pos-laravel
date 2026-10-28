<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->low, fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('sku')->paginate(15)->withQueryString();

        return view('products.index', ['products' => $products, 'categories' => Category::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('products.form', ['product' => new Product(['min_stock' => 5, 'is_active' => true]), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $initial = (int) ($data['stock'] ?? 0);
        $product = Product::create([...$data, 'stock' => 0]);
        if ($initial > 0) {
            $product->moveStock($initial, 'in', auth()->id(), 'สต็อกเริ่มต้น');
        }

        return redirect()->route('products.index')->with('success', 'เพิ่มสินค้าเรียบร้อย');
    }

    public function edit(Product $product): View
    {
        return view('products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get(), 'movements' => $product->movements()->with('user')->limit(10)->get()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        unset($data['stock']); // การเปลี่ยนจำนวนต้องทำผ่านเมนูปรับสต็อกเท่านั้น
        $product->update($data);

        return redirect()->route('products.index')->with('success', 'แก้ไขสินค้าเรียบร้อย');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleItems()->exists()) {
            $product->update(['is_active' => false]);

            return back()->with('success', 'สินค้ามีประวัติการขาย จึงปิดการขายแทนการลบ');
        }
        $product->delete();

        return back()->with('success', 'ลบสินค้าเรียบร้อย');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['in', 'out', 'adjust'])],
            'qty' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $delta = match ($data['type']) {
            'in' => $data['qty'],
            'out' => -$data['qty'],
            'adjust' => $data['qty'] - $product->stock, // qty = ยอดคงเหลือจริง
        };

        if ($product->stock + $delta < 0) {
            return back()->with('error', 'จำนวนตัดออกมากกว่าสต็อกคงเหลือ');
        }
        if ($delta !== 0) {
            $product->moveStock($delta, $data['type'], auth()->id(), $data['note'] ?? null);
        }

        return back()->with('success', 'ปรับสต็อกเรียบร้อย');
    }

    public function movements(Request $request): View
    {
        $movements = StockMovement::with(['product', 'user'])
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->latest()->paginate(20)->withQueryString();

        return view('products.movements', compact('movements'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:40', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ]);
        $data['cost'] ??= 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
