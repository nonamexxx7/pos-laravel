@extends('layouts.app')
@section('title', 'พนักงาน')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <form class="d-flex gap-2" method="GET">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหา ชื่อ / อีเมล">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> เพิ่มพนักงาน</a>
</div>
<div class="card"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>ชื่อ</th><th>อีเมล</th><th>ตำแหน่ง</th><th>เบอร์โทร</th><th>สิทธิ์</th><th>สถานะ</th><th></th></tr></thead>
    @foreach ($employees as $e)
        <tr>
            <td>{{ $e->name }}</td><td>{{ $e->email }}</td><td>{{ $e->position ?? '-' }}</td><td>{{ $e->phone ?? '-' }}</td>
            <td><span class="badge {{ $e->isAdmin() ? 'bg-danger' : 'bg-secondary' }}">{{ $e->isAdmin() ? 'ผู้ดูแลระบบ' : 'พนักงานขาย' }}</span></td>
            <td>{!! $e->is_active ? '<span class="badge bg-success">ใช้งาน</span>' : '<span class="badge bg-dark">ระงับ</span>' !!}</td>
            <td class="text-end text-nowrap">
                <a href="{{ route('employees.edit', $e) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                @unless ($e->is(auth()->user()))
                <form method="POST" action="{{ route('employees.destroy', $e) }}" class="d-inline" onsubmit="return confirm('ลบพนักงานนี้?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endunless
            </td>
        </tr>
    @endforeach
</table></div>
<div class="mt-3">{{ $employees->links('pagination::bootstrap-5') }}</div>
@endsection
