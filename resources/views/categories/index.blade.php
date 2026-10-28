@extends('layouts.app')
@section('title', 'หมวดหมู่สินค้า')
@section('content')
<div class="row g-3">
    <div class="col-md-4"><div class="card"><div class="card-body">
        <form method="POST" action="{{ route('categories.store') }}">@csrf
            <label class="form-label">เพิ่มหมวดหมู่ใหม่</label>
            <div class="input-group"><input name="name" class="form-control" required><button class="btn btn-primary">เพิ่ม</button></div>
        </form></div></div></div>
    <div class="col-md-8"><div class="card"><table class="table mb-0 align-middle">
        <thead><tr><th>ชื่อหมวดหมู่</th><th>จำนวนสินค้า</th><th></th></tr></thead>
        @foreach ($categories as $c)
            <tr>
                <td>
                    <form method="POST" action="{{ route('categories.update', $c) }}" class="d-flex gap-2">@csrf @method('PUT')
                        <input name="name" value="{{ $c->name }}" class="form-control form-control-sm"><button class="btn btn-sm btn-outline-primary">บันทึก</button>
                    </form>
                </td>
                <td>{{ $c->products_count }}</td>
                <td class="text-end"><form method="POST" action="{{ route('categories.destroy', $c) }}" onsubmit="return confirm('ลบหมวดหมู่นี้?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td>
            </tr>
        @endforeach
    </table></div></div>
</div>
@endsection
