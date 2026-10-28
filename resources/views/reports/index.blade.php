@extends('layouts.app')
@section('title', 'รายงานการขาย')
@section('content')
<form class="d-flex gap-2 align-items-end mb-3 flex-wrap no-print" method="GET">
    <div><label class="form-label small mb-0">ตั้งแต่</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control"></div>
    <div><label class="form-label small mb-0">ถึง</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control"></div>
    <button class="btn btn-primary">แสดงรายงาน</button>
    <a class="btn btn-outline-success" href="{{ route('reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"><i class="bi bi-filetype-csv"></i> ส่งออก CSV</a>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> พิมพ์</button>
</form>

<div class="row g-3 mb-3">
    @foreach ([['ยอดขายรวม', '฿' . number_format($summary['revenue'], 2)], ['จำนวนบิล', number_format($summary['bills'])], ['เฉลี่ยต่อบิล', '฿' . number_format($summary['avg'], 2)], ['กำไรขั้นต้น', '฿' . number_format($summary['profit'], 2)], ['ต้นทุนสินค้า', '฿' . number_format($summary['cost'], 2)], ['บิลที่ยกเลิก', number_format($summary['voided'])]] as [$l, $v])
        <div class="col-6 col-lg-2"><div class="card"><div class="card-body py-2"><div class="small text-muted">{{ $l }}</div><div class="fw-bold fs-5">{{ $v }}</div></div></div></div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-7"><div class="card"><div class="card-header bg-white fw-bold">ยอดขายรายวัน</div>
        <div class="card-body"><canvas id="dailyChart" height="120"></canvas></div>
        <table class="table table-sm mb-0"><thead><tr><th>วันที่</th><th class="text-end">จำนวนบิล</th><th class="text-end">ยอดขาย</th></tr></thead>
            @forelse ($daily as $d)<tr><td>{{ \Illuminate\Support\Carbon::parse($d->day)->format('d/m/Y') }}</td><td class="text-end">{{ $d->bills }}</td><td class="text-end">{{ number_format($d->total, 2) }}</td></tr>
            @empty<tr><td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูลในช่วงเวลานี้</td></tr>@endforelse
        </table></div></div>
    <div class="col-lg-5">
        <div class="card mb-3"><div class="card-header bg-white fw-bold">สินค้าขายดี 10 อันดับ</div>
            <table class="table table-sm mb-0"><thead><tr><th>สินค้า</th><th class="text-end">จำนวน</th><th class="text-end">ยอดขาย</th></tr></thead>
                @forelse ($topProducts as $p)<tr><td>{{ $p->name }}</td><td class="text-end">{{ $p->qty }}</td><td class="text-end">{{ number_format($p->total, 2) }}</td></tr>
                @empty<tr><td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>@endforelse</table></div>
        <div class="card mb-3"><div class="card-header bg-white fw-bold">แยกตามช่องทางชำระเงิน</div>
            <table class="table table-sm mb-0">
                @forelse ($byMethod as $m)<tr><td>{{ \App\Models\Payment::METHODS[$m->method] ?? $m->method }}</td><td class="text-end">{{ $m->bills }} บิล</td><td class="text-end">{{ number_format($m->total, 2) }}</td></tr>
                @empty<tr><td class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>@endforelse</table></div>
        <div class="card"><div class="card-header bg-white fw-bold">ยอดขายแยกตามพนักงาน</div>
            <table class="table table-sm mb-0">
                @forelse ($byCashier as $c)<tr><td>{{ $c->name }}</td><td class="text-end">{{ $c->bills }} บิล</td><td class="text-end">{{ number_format($c->total, 2) }}</td></tr>
                @empty<tr><td class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>@endforelse</table></div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: { labels: @json($daily->pluck('day')), datasets: [{ label: 'ยอดขาย (บาท)', data: @json($daily->pluck('total')->map(fn ($v) => (float) $v)), borderColor: '#3b5998', backgroundColor: 'rgba(59,89,152,.15)', fill: true, tension: .3 }] },
    options: { animation: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
