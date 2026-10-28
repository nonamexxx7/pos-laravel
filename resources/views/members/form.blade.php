@extends('layouts.app')
@section('title', $member->exists ? 'แก้ไขสมาชิก ' . $member->code : 'เพิ่มสมาชิกใหม่')
@section('content')
<div class="card" style="max-width:560px"><div class="card-body">
    <form method="POST" action="{{ $member->exists ? route('members.update', $member) : route('members.store') }}">
        @csrf @if ($member->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">ชื่อ-นามสกุล *</label><input name="name" class="form-control" value="{{ old('name', $member->name) }}" required></div>
        <div class="mb-3"><label class="form-label">เบอร์โทร *</label><input name="phone" class="form-control" value="{{ old('phone', $member->phone) }}" required></div>
        <div class="mb-3"><label class="form-label">อีเมล</label><input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}"></div>
        <button class="btn btn-primary">บันทึก</button>
        <a href="{{ route('members.index') }}" class="btn btn-light">ยกเลิก</a>
    </form>
</div></div>
@endsection
