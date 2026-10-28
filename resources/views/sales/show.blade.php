@extends('layouts.app')
@section('title', 'รายละเอียดบิล ' . $sale->invoice_no)
@section('content')
<div class="card" style="max-width:820px"><div class="card-body">
    <div class="d-flex justify-content-between">
        <div>
            <h5>{{ $sale->invoice_no }} @if ($sale->status === 'void')<span class="badge bg-secondary">ยกเลิกแล้ว</span>@endif</h5>
            <div class="text-muted">{{ $sale->created_at->format('d/m/Y H:i') }} · พนักงาน: {{ $sale->user->name }} · สมาชิก: {{ $sale->member?->name ?? 'ไม่ระบุ' }}</div>
        </div>
        <div class="no-print">
            <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-printer"></i> ใบเสร็จ</a>
            @if (auth()->user()->isAdmin() && $sale->status !== 'void')
            <form method="POST" action="{{ route('sales.void', $sale) }}" class="d-inline" onsubmit="return confirm('ยกเลิกบิลนี้และคืนสต็อก?')">@csrf
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> ยกเลิกบิล</button></form>
            @endif
        </div>
    </div>
    <table class="table mt-3">
        <thead><tr><th>สินค้า</th><th class="text-end">ราคา</th><th class="text-end">จำนวน</th><th class="text-end">รวม</th></tr></thead>
        @foreach ($sale->items as $i)
            <tr><td>{{ $i->name }}</td><td class="text-end">{{ number_format($i->price, 2) }}</td><td class="text-end">{{ $i->qty }}</td><td class="text-end">{{ number_format($i->total, 2) }}</td></tr>
        @endforeach
        <tr><td colspan="3" class="text-end">รวมเป็นเงิน</td><td class="text-end">{{ number_format($sale->subtotal, 2) }}</td></tr>
        <tr><td colspan="3" class="text-end">ส่วนลด (ใช้ {{ $sale->points_used }} แต้ม)</td><td class="text-end">-{{ number_format($sale->discount, 2) }}</td></tr>
        <tr class="fw-bold"><td colspan="3" class="text-end">ยอดสุทธิ</td><td class="text-end">{{ number_format($sale->total, 2) }}</td></tr>
    </table>
    @if ($sale->payment)
        <div>ชำระโดย: <b>{{ \App\Models\Payment::METHODS[$sale->payment->method] }}</b> · รับ {{ number_format($sale->payment->received, 2) }} · ทอน {{ number_format($sale->payment->change, 2) }}
            @if ($sale->payment->reference) · อ้างอิง {{ $sale->payment->reference }}@endif</div>
    @endif
    @if ($sale->member)<div class="text-muted mt-1">แต้มที่ได้รับ: +{{ $sale->points_earned }}</div>@endif
    <a href="{{ route('sales.index') }}" class="btn btn-light mt-3 no-print">กลับ</a>
</div></div>
@endsection
