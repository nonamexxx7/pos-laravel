@extends('layouts.app')
@section('title', 'ความเคลื่อนไหวสต็อก')
@section('content')
<form class="mb-3 d-flex gap-2" method="GET" style="max-width:360px">
    <select name="type" class="form-select" onchange="this.form.submit()"><option value="">ทุกประเภท</option>
        @foreach (\App\Models\StockMovement::TYPES as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach</select>
</form>
<div class="card"><table class="table table-hover mb-0">
    <thead><tr><th>วันเวลา</th><th>สินค้า</th><th>ประเภท</th><th class="text-end">จำนวน</th><th class="text-end">คงเหลือ</th><th>ผู้ทำรายการ</th><th>หมายเหตุ</th></tr></thead>
    @forelse ($movements as $m)
        <tr><td>{{ $m->created_at->format('d/m/Y H:i') }}</td><td>{{ $m->product->name }}</td><td>{{ \App\Models\StockMovement::TYPES[$m->type] ?? $m->type }}</td>
            <td class="text-end {{ $m->qty > 0 ? 'text-success' : 'text-danger' }}">{{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}</td><td class="text-end">{{ $m->balance }}</td>
            <td>{{ $m->user?->name ?? '-' }}</td><td>{{ $m->note }}</td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">ไม่มีข้อมูล</td></tr>
    @endforelse
</table></div>
<div class="mt-3">{{ $movements->links('pagination::bootstrap-5') }}</div>
@endsection
