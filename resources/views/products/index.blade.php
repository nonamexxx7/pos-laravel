@extends('layouts.app')
@section('title', 'สินค้าและสต็อก')
@section('content')
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2" method="GET">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหา ชื่อ / รหัสสินค้า">
        <select name="category" class="form-select" style="width:170px"><option value="">ทุกหมวดหมู่</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach</select>
        <div class="form-check align-self-center text-nowrap"><input type="checkbox" class="form-check-input" name="low" value="1" id="low" @checked(request('low')) onchange="this.form.submit()"><label for="low" class="form-check-label">ใกล้หมด</label></div>
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    @if (auth()->user()->isAdmin())<a href="{{ route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> เพิ่มสินค้า</a>@endif
</div>
<div class="card"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>รหัส</th><th>ชื่อสินค้า</th><th>หมวดหมู่</th><th class="text-end">ราคา</th><th class="text-end">ทุน</th><th class="text-end">คงเหลือ</th><th>สถานะ</th>@if (auth()->user()->isAdmin())<th></th>@endif</tr></thead>
    @forelse ($products as $p)
        <tr class="{{ $p->is_active ? '' : 'table-secondary' }}">
            <td>{{ $p->sku }}</td><td>{{ $p->name }}</td><td>{{ $p->category?->name ?? '-' }}</td>
            <td class="text-end">{{ number_format($p->price, 2) }}</td><td class="text-end">{{ number_format($p->cost, 2) }}</td>
            <td class="text-end"><span class="badge {{ $p->stock <= 0 ? 'bg-danger' : ($p->isLow() ? 'bg-warning text-dark' : 'bg-success') }}">{{ $p->stock }}</span></td>
            <td>{{ $p->is_active ? 'ขายอยู่' : 'ปิดการขาย' }}</td>
            @if (auth()->user()->isAdmin())
            <td class="text-end text-nowrap">
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#stockModal" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-stock="{{ $p->stock }}"><i class="bi bi-box-arrow-in-down"></i> ปรับสต็อก</button>
                <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('ลบสินค้านี้?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted py-4">ไม่พบสินค้า</td></tr>
    @endforelse
</table></div>
<div class="mt-3">{{ $products->links('pagination::bootstrap-5') }}</div>

@if (auth()->user()->isAdmin())
<div class="modal fade" id="stockModal" tabindex="-1"><div class="modal-dialog"><form method="POST" id="stockForm" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">ปรับสต็อก: <span id="sName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p>คงเหลือปัจจุบัน: <b id="sStock"></b></p>
        <div class="mb-3"><label class="form-label">ประเภท</label>
            <select name="type" class="form-select"><option value="in">รับสินค้าเข้า (+)</option><option value="out">ตัดออก/ชำรุด (-)</option><option value="adjust">ปรับยอดตามนับจริง (=)</option></select></div>
        <div class="mb-3"><label class="form-label">จำนวน (ถ้าเลือก “ปรับยอด” ให้ใส่ยอดคงเหลือที่นับได้)</label><input type="number" name="qty" min="0" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">หมายเหตุ</label><input name="note" class="form-control"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">บันทึก</button></div>
</form></div></div>
@push('scripts')
<script>
document.getElementById('stockModal').addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    document.getElementById('sName').textContent = b.dataset.name;
    document.getElementById('sStock').textContent = b.dataset.stock;
    document.getElementById('stockForm').action = '{{ url('/products') }}/' + b.dataset.id + '/stock';
});
</script>
@endpush
@endif
@endsection
