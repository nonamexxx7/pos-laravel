<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $sales = $this->base($from, $to);
        $summary = [
            'bills' => (clone $sales)->count(),
            'revenue' => (clone $sales)->sum('total'),
            'discount' => (clone $sales)->sum('discount'),
            'voided' => Sale::where('status', 'void')->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
        ];

        $items = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $summary['cost'] = (clone $items)->sum(DB::raw('sale_items.cost * sale_items.qty'));
        $summary['profit'] = (clone $items)->sum(DB::raw('(sale_items.price - sale_items.cost) * sale_items.qty')) - $summary['discount'];
        $summary['avg'] = $summary['bills'] ? $summary['revenue'] / $summary['bills'] : 0;

        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'daily' => (clone $sales)->selectRaw('date(created_at) as day, count(*) as bills, sum(total) as total')->groupBy('day')->orderBy('day')->get(),
            'topProducts' => (clone $items)->select('sale_items.name', DB::raw('sum(sale_items.qty) as qty'), DB::raw('sum(sale_items.total) as total'))
                ->groupBy('sale_items.name')->orderByDesc('qty')->limit(10)->get(),
            'byMethod' => Payment::join('sales', 'sales.id', '=', 'payments.sale_id')->where('sales.status', 'completed')
                ->whereBetween('sales.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->select('payments.method', DB::raw('count(*) as bills'), DB::raw('sum(payments.amount) as total'))->groupBy('payments.method')->get(),
            'byCashier' => (clone $sales)->join('users', 'users.id', '=', 'sales.user_id')
                ->select('users.name', DB::raw('count(*) as bills'), DB::raw('sum(sales.total) as total'))->groupBy('users.name')->orderByDesc('total')->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $rows = $this->base($from, $to)->with(['user', 'member', 'payment'])->orderBy('created_at')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM ให้ Excel อ่านภาษาไทยได้
            fputcsv($out, ['เลขที่บิล', 'วันที่', 'พนักงาน', 'สมาชิก', 'ยอดรวม', 'ส่วนลด', 'สุทธิ', 'ช่องทางชำระ']);
            foreach ($rows as $s) {
                fputcsv($out, [$s->invoice_no, $s->created_at->format('Y-m-d H:i'), $s->user->name, $s->member?->name, $s->subtotal, $s->discount, $s->total, Payment::METHODS[$s->payment?->method] ?? '-']);
            }
            fclose($out);
        }, "sales-{$from->toDateString()}_{$to->toDateString()}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        return [$from, $to];
    }

    private function base($from, $to)
    {
        return Sale::where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }
}
