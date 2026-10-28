<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    /** ทุก 100 บาท ได้ 1 แต้ม และ 1 แต้ม = ส่วนลด 1 บาท */
    private const BAHT_PER_POINT = 100;

    public function create(): View
    {
        return view('sales.pos', [
            'products' => Product::with('category')->where('is_active', true)->orderBy('name')->get()
                ->map(fn ($p) => ['id' => $p->id, 'sku' => $p->sku, 'name' => $p->name, 'price' => (float) $p->price, 'stock' => $p->stock, 'category' => $p->category?->name ?? 'อื่น ๆ']),
            'categories' => Category::orderBy('name')->pluck('name'),
            'members' => Member::orderBy('name')->get(['id', 'code', 'name', 'phone', 'points']),
            'methods' => Payment::METHODS,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'member_id' => ['nullable', 'exists:members,id'],
            'use_points' => ['nullable', 'integer', 'min:0'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'received' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $sale = DB::transaction(function () use ($data) {
            $subtotal = 0;
            $lines = [];
            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                if (! $product->is_active) {
                    throw ValidationException::withMessages(['items' => "สินค้า {$product->name} ปิดการขาย"]);
                }
                if ($product->stock < $item['qty']) {
                    throw ValidationException::withMessages(['items' => "สินค้า {$product->name} คงเหลือไม่พอ (เหลือ {$product->stock})"]);
                }
                $lineTotal = $product->price * $item['qty'];
                $subtotal += $lineTotal;
                $lines[] = [$product, $item['qty'], $lineTotal];
            }

            $member = ! empty($data['member_id']) ? Member::lockForUpdate()->find($data['member_id']) : null;
            $pointsUsed = 0;
            if ($member) {
                $pointsUsed = min((int) ($data['use_points'] ?? 0), $member->points, (int) floor($subtotal));
            }
            $total = $subtotal - $pointsUsed;

            $received = (float) ($data['received'] ?? 0);
            if ($data['method'] !== 'cash') {
                $received = $total;
            }
            if ($received < $total) {
                throw ValidationException::withMessages(['received' => 'จำนวนเงินที่รับน้อยกว่ายอดที่ต้องชำระ']);
            }

            $earned = $member ? (int) floor($total / self::BAHT_PER_POINT) : 0;

            $sale = Sale::create([
                'invoice_no' => Sale::nextInvoiceNo(),
                'user_id' => auth()->id(),
                'member_id' => $member?->id,
                'subtotal' => $subtotal,
                'discount' => $pointsUsed,
                'total' => $total,
                'points_earned' => $earned,
                'points_used' => $pointsUsed,
            ]);

            foreach ($lines as [$product, $qty, $lineTotal]) {
                $sale->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'qty' => $qty,
                    'total' => $lineTotal,
                ]);
                $product->moveStock(-$qty, 'sale', auth()->id(), $sale->invoice_no);
            }

            $sale->payment()->create([
                'method' => $data['method'],
                'amount' => $total,
                'received' => $received,
                'change' => $received - $total,
                'reference' => $data['reference'] ?? null,
            ]);

            if ($member) {
                $member->update([
                    'points' => $member->points - $pointsUsed + $earned,
                    'total_spent' => $member->total_spent + $total,
                ]);
            }

            return $sale;
        });

        return response()->json(['receipt' => route('sales.receipt', $sale), 'invoice_no' => $sale->invoice_no]);
    }

    public function index(Request $request): View
    {
        $sales = Sale::with(['user', 'member', 'payment'])
            ->when(! auth()->user()->isAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->q, fn ($q, $s) => $q->where('invoice_no', 'like', "%$s%"))
            ->when($request->date, fn ($q, $d) => $q->whereDate('created_at', $d))
            ->latest()->paginate(15)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale): View
    {
        $this->authorizeSale($sale);

        return view('sales.show', ['sale' => $sale->load(['items', 'user', 'member', 'payment'])]);
    }

    public function receipt(Sale $sale): View
    {
        $this->authorizeSale($sale);

        return view('sales.receipt', ['sale' => $sale->load(['items', 'user', 'member', 'payment'])]);
    }

    public function void(Sale $sale): RedirectResponse
    {
        if ($sale->status === 'void') {
            return back()->with('error', 'บิลนี้ถูกยกเลิกไปแล้ว');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $item->product?->moveStock($item->qty, 'void', auth()->id(), 'ยกเลิก '.$sale->invoice_no);
            }
            if ($sale->member) {
                $sale->member->update([
                    'points' => max(0, $sale->member->points + $sale->points_used - $sale->points_earned),
                    'total_spent' => max(0, $sale->member->total_spent - $sale->total),
                ]);
            }
            $sale->update(['status' => 'void']);
        });

        return back()->with('success', 'ยกเลิกบิลและคืนสต็อกเรียบร้อย');
    }

    private function authorizeSale(Sale $sale): void
    {
        abort_unless(auth()->user()->isAdmin() || $sale->user_id === auth()->id(), 403);
    }
}
