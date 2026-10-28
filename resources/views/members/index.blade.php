@extends('layouts.app')
@section('title', 'สมาชิก')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <form class="d-flex gap-2" method="GET">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ค้นหา ชื่อ / เบอร์ / รหัส">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('members.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> เพิ่มสมาชิก</a>
</div>
<div class="card"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>รหัส</th><th>ชื่อ</th><th>เบอร์โทร</th><th>ระดับ</th><th class="text-end">แต้มสะสม</th><th class="text-end">ยอดซื้อรวม</th><th></th></tr></thead>
    @forelse ($members as $m)
        <tr>
            <td>{{ $m->code }}</td><td><a href="{{ route('members.show', $m) }}">{{ $m->name }}</a></td><td>{{ $m->phone }}</td>
            <td><span class="badge {{ ['Gold' => 'bg-warning text-dark', 'Silver' => 'bg-secondary', 'Standard' => 'bg-light text-dark border'][$m->level] }}">{{ $m->level }}</span></td>
            <td class="text-end">{{ number_format($m->points) }}</td><td class="text-end">{{ number_format($m->total_spent, 2) }}</td>
            <td class="text-end text-nowrap">
                <a href="{{ route('members.edit', $m) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('members.destroy', $m) }}" class="d-inline" onsubmit="return confirm('ลบสมาชิกนี้?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">ไม่พบข้อมูลสมาชิก</td></tr>
    @endforelse
</table></div>
<div class="mt-3">{{ $members->links('pagination::bootstrap-5') }}</div>
@endsection
