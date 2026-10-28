@extends('layouts.app')
@section('title', 'ข้อมูลสมาชิก')
@section('content')
<div class="row g-3">
    <div class="col-lg-4"><div class="card"><div class="card-body">
        <h5>{{ $member->name }} <span class="badge bg-primary">{{ $member->level }}</span></h5>
        <p class="text-muted mb-2">รหัส {{ $member->code }}</p>
        <p class="mb-1"><i class="bi bi-telephone"></i> {{ $member->phone }}</p>
        <p class="mb-3"><i class="bi bi-envelope"></i> {{ $member->email ?? '-' }}</p>
        <div class="d-flex justify-content-between"><span>แต้มสะสม</span><b>{{ number_format($member->points) }}</b></div>
        <div class="d-flex justify-content-between"><span>ยอดซื้อรวม</span><b>฿{{ number_format($member->total_spent, 2) }}</b></div>
        <hr><small class="text-muted">ซื้อครบทุก 100 บาท รับ 1 แต้ม / ใช้ 1 แต้ม = ส่วนลด 1 บาท<br>Silver ≥ 5,000 บาท, Gold ≥ 20,000 บาท</small>
        <div class="mt-3"><a href="{{ route('members.edit', $member) }}" class="btn btn-outline-primary btn-sm">แก้ไข</a> <a href="{{ route('members.index') }}" class="btn btn-light btn-sm">กลับ</a></div>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="card-header bg-white fw-bold">ประวัติการซื้อ</div>
        <table class="table mb-0">
            <thead><tr><th>เลขที่บิล</th><th>วันที่</th><th class="text-end">ยอดสุทธิ</th><th class="text-end">แต้มได้/ใช้</th></tr></thead>
            @forelse ($sales as $s)
                <tr><td><a href="{{ route('sales.show', $s) }}">{{ $s->invoice_no }}</a> @if ($s->status === 'void')<span class="badge bg-secondary">ยกเลิก</span>@endif</td>
                    <td>{{ $s->created_at->format('d/m/Y H:i') }}</td><td class="text-end">{{ number_format($s->total, 2) }}</td>
                    <td class="text-end">+{{ $s->points_earned }} / -{{ $s->points_used }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">ยังไม่มีประวัติการซื้อ</td></tr>
            @endforelse
        </table></div>
        <div class="mt-3">{{ $sales->links('pagination::bootstrap-5') }}</div></div>
</div>
@endsection
