@extends('layouts.app')
@section('title', 'ประวัติการขาย')
@section('content')
<form class="d-flex gap-2 mb-3" method="GET" style="max-width:520px">
    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="เลขที่บิล">
    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>
<div class="card"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>เลขที่บิล</th><th>วันเวลา</th><th>พนักงาน</th><th>สมาชิก</th><th>ชำระโดย</th><th class="text-end">ยอดสุทธิ</th><th>สถานะ</th></tr></thead>
    @forelse ($sales as $s)
        <tr>
            <td><a href="{{ route('sales.show', $s) }}">{{ $s->invoice_no }}</a></td><td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $s->user->name }}</td><td>{{ $s->member?->name ?? '-' }}</td><td>{{ \App\Models\Payment::METHODS[$s->payment?->method] ?? '-' }}</td>
            <td class="text-end">{{ number_format($s->total, 2) }}</td>
            <td>@if ($s->status === 'void')<span class="badge bg-secondary">ยกเลิก</span>@else<span class="badge bg-success">สำเร็จ</span>@endif</td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">ไม่พบรายการขาย</td></tr>
    @endforelse
</table></div>
<div class="mt-3">{{ $sales->links('pagination::bootstrap-5') }}</div>
@endsection
