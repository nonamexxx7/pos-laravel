<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบเสร็จ {{ $sale->invoice_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #eee; }
        .slip { width: 320px; margin: 20px auto; background: #fff; padding: 16px; font-size: 14px; }
        .slip table { width: 100%; }
        @media print { body { background: #fff; } .slip { margin: 0; width: 100%; } .no-print { display: none; } }
    </style>
</head>
<body>
<div class="slip">
    <div class="text-center"><b class="fs-5">ร้านตัวอย่าง POS</b><br>ใบเสร็จรับเงิน / ใบกำกับภาษีอย่างย่อ</div>
    <hr>
    เลขที่: {{ $sale->invoice_no }}<br>วันที่: {{ $sale->created_at->format('d/m/Y H:i') }}<br>
    พนักงาน: {{ $sale->user->name }}<br>@if ($sale->member)สมาชิก: {{ $sale->member->name }} ({{ $sale->member->code }})<br>@endif
    <hr>
    <table>
        @foreach ($sale->items as $i)
            <tr><td colspan="2">{{ $i->name }}</td></tr>
            <tr><td class="ps-3">{{ $i->qty }} x {{ number_format($i->price, 2) }}</td><td class="text-end">{{ number_format($i->total, 2) }}</td></tr>
        @endforeach
    </table>
    <hr>
    <table>
        <tr><td>รวมเป็นเงิน</td><td class="text-end">{{ number_format($sale->subtotal, 2) }}</td></tr>
        @if ($sale->discount > 0)<tr><td>ส่วนลดแต้ม ({{ $sale->points_used }})</td><td class="text-end">-{{ number_format($sale->discount, 2) }}</td></tr>@endif
        <tr class="fw-bold fs-6"><td>ยอดสุทธิ</td><td class="text-end">{{ number_format($sale->total, 2) }}</td></tr>
        <tr><td>{{ \App\Models\Payment::METHODS[$sale->payment->method] }}</td><td class="text-end">{{ number_format($sale->payment->received, 2) }}</td></tr>
        <tr><td>เงินทอน</td><td class="text-end">{{ number_format($sale->payment->change, 2) }}</td></tr>
    </table>
    @if ($sale->member)<hr>แต้มที่ได้รับ: {{ $sale->points_earned }} · คงเหลือ: {{ $sale->member->points }}@endif
    @if ($sale->status === 'void')<div class="text-center text-danger fw-bold mt-2">*** ยกเลิกบิลแล้ว ***</div>@endif
    <hr><div class="text-center">ขอบคุณที่ใช้บริการ</div>
    <div class="no-print text-center mt-3 d-grid gap-2">
        <button onclick="window.print()" class="btn btn-primary">พิมพ์ใบเสร็จ</button>
        <a href="{{ route('pos') }}" class="btn btn-success">ขายรายการต่อไป</a>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-light">รายละเอียดบิล</a>
    </div>
</div>
</body>
</html>
