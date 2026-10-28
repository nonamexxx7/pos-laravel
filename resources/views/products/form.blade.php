@extends('layouts.app')
@section('title', $product->exists ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่')
@section('content')
<div class="row g-3">
<div class="col-lg-7"><div class="card"><div class="card-body">
    <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
        @csrf @if ($product->exists) @method('PUT') @endif
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">รหัสสินค้า (SKU) *</label><input name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required></div>
            <div class="col-md-8 mb-3"><label class="form-label">ชื่อสินค้า *</label><input name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">หมวดหมู่</label>
                <select name="category_id" class="form-select"><option value="">-- ไม่ระบุ --</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-6 mb-3"><label class="form-label">แจ้งเตือนเมื่อเหลือไม่เกิน *</label><input type="number" min="0" name="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock) }}" required></div>
            <div class="col-md-4 mb-3"><label class="form-label">ราคาขาย *</label><input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $product->price) }}" required></div>
            <div class="col-md-4 mb-3"><label class="form-label">ราคาทุน</label><input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', $product->cost) }}"></div>
            <div class="col-md-4 mb-3"><label class="form-label">จำนวนคงเหลือ</label>
                @if ($product->exists)<input class="form-control" value="{{ $product->stock }}" disabled title="ปรับผ่านปุ่มปรับสต็อก">
                @else<input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', 0) }}">@endif</div>
        </div>
        <div class="form-check form-switch mb-3"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="act" @checked(old('is_active', $product->is_active))><label class="form-check-label" for="act">เปิดขายสินค้านี้</label></div>
        <button class="btn btn-primary">บันทึก</button>
        <a href="{{ route('products.index') }}" class="btn btn-light">ยกเลิก</a>
    </form>
</div></div></div>
@isset($movements)
<div class="col-lg-5"><div class="card"><div class="card-header bg-white fw-bold">ประวัติสต็อกล่าสุด</div>
    <table class="table table-sm mb-0">
        @forelse ($movements as $m)
            <tr><td>{{ $m->created_at->format('d/m H:i') }}</td><td>{{ \App\Models\StockMovement::TYPES[$m->type] }}</td>
                <td class="text-end {{ $m->qty > 0 ? 'text-success' : 'text-danger' }}">{{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}</td><td class="text-end">={{ $m->balance }}</td></tr>
        @empty
            <tr><td class="text-muted text-center py-3">ยังไม่มีประวัติ</td></tr>
        @endforelse
    </table></div></div>
@endisset
</div>
@endsection
