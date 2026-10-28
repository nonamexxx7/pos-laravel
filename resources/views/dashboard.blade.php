@extends('layouts.app')
@section('title', 'แดชบอร์ด')
@section('content')
<div class="row g-3 mb-4">
    @foreach ([
        ['ยอดขายวันนี้', '฿' . number_format($todayTotal, 2), 'bi-cash-coin', 'primary'],
        ['จำนวนบิลวันนี้', number_format($todayBills) . ' บิล', 'bi-receipt', 'success'],
        ['ยอดขายเดือนนี้', '฿' . number_format($monthTotal, 2), 'bi-graph-up-arrow', 'warning'],
        ['สมาชิกทั้งหมด', number_format($memberCount) . ' คน', 'bi-people', 'info'],
    ] as [$label, $value, $icon, $color])
        <div class="col-6 col-xl-3">
            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                <div><div class="text-muted small">{{ $label }}</div><div class="fs-4 fw-bold">{{ $value }}</div></div>
                <i class="bi {{ $icon }} stat-icon text-{{ $color }}"></i>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header bg-white fw-bold">ยอดขาย 7 วันล่าสุด</div>
            <div class="card-body"><canvas id="salesChart" height="110"></canvas></div></div>
        <div class="card"><div class="card-header bg-white fw-bold">บิลล่าสุด</div>
            <table class="table mb-0 align-middle">
                <thead><tr><th>เลขที่บิล</th><th>เวลา</th><th>พนักงาน</th><th class="text-end">ยอดสุทธิ</th><th>สถานะ</th></tr></thead>
                @forelse ($recentSales as $s)
                    <tr><td><a href="{{ route('sales.show', $s) }}">{{ $s->invoice_no }}</a></td><td>{{ $s->created_at->format('d/m H:i') }}</td><td>{{ $s->user->name }}</td>
                        <td class="text-end">{{ number_format($s->total, 2) }}</td>
                        <td>@if ($s->status === 'void')<span class="badge bg-secondary">ยกเลิก</span>@else<span class="badge bg-success">สำเร็จ</span>@endif</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">ยังไม่มีการขาย</td></tr>
                @endforelse
            </table></div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header bg-white fw-bold"><i class="bi bi-exclamation-triangle text-danger"></i> สินค้าใกล้หมด ({{ $lowStock->count() }})</div>
            <ul class="list-group list-group-flush">
                @forelse ($lowStock as $p)
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $p->name }}</span>
                        <span class="badge {{ $p->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">เหลือ {{ $p->stock }}</span></li>
                @empty
                    <li class="list-group-item text-muted">สต็อกปกติทุกรายการ</li>
                @endforelse
            </ul></div>
        <div class="card"><div class="card-header bg-white fw-bold"><i class="bi bi-trophy text-warning"></i> สินค้าขายดี (30 วัน)</div>
            <ul class="list-group list-group-flush">
                @forelse ($topProducts as $i => $p)
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $i + 1 }}. {{ $p->name }}</span><b>{{ $p->qty }} ชิ้น</b></li>
                @empty
                    <li class="list-group-item text-muted">ยังไม่มีข้อมูล</li>
                @endforelse
            </ul></div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: { labels: @json($chartLabels), datasets: [{ label: 'ยอดขาย (บาท)', data: @json($chartData), backgroundColor: '#3b5998', borderRadius: 4 }] },
    options: { animation: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
