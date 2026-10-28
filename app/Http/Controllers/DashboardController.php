<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $completed = Sale::where('status', 'completed');

        $today = (clone $completed)->whereDate('created_at', today());
        $month = (clone $completed)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);

        $days = collect(range(6, 0))->map(fn ($i) => today()->subDays($i));
        $totals = (clone $completed)->where('created_at', '>=', today()->subDays(6))
            ->selectRaw('date(created_at) as d, sum(total) as t')->groupBy('d')->pluck('t', 'd');

        $topProducts = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.created_at', '>=', today()->subDays(29))
            ->select('sale_items.name', DB::raw('sum(sale_items.qty) as qty'))
            ->groupBy('sale_items.name')->orderByDesc('qty')->limit(5)->get();

        return view('dashboard', [
            'todayTotal' => (clone $today)->sum('total'),
            'todayBills' => (clone $today)->count(),
            'monthTotal' => (clone $month)->sum('total'),
            'memberCount' => Member::count(),
            'lowStock' => Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->get(),
            'chartLabels' => $days->map->format('d/m')->values(),
            'chartData' => $days->map(fn ($d) => (float) ($totals[$d->toDateString()] ?? 0))->values(),
            'topProducts' => $topProducts,
            'recentSales' => Sale::with('user')->latest()->limit(5)->get(),
        ]);
    }
}
