@extends('layouts.app')
@section('title', 'โปรไฟล์ของฉัน')
@section('content')
<div class="card" style="max-width:560px"><div class="card-body">
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label">อีเมล</label><input class="form-control" value="{{ auth()->user()->email }}" disabled></div>
        <div class="mb-3"><label class="form-label">ชื่อ</label><input name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required></div>
        <div class="mb-3"><label class="form-label">เบอร์โทร</label><input name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}"></div>
        <hr>
        <p class="text-muted small">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</p>
        <div class="mb-3"><label class="form-label">รหัสผ่านใหม่</label><input type="password" name="password" class="form-control"></div>
        <div class="mb-3"><label class="form-label">ยืนยันรหัสผ่านใหม่</label><input type="password" name="password_confirmation" class="form-control"></div>
        <button class="btn btn-primary">บันทึก</button>
    </form>
</div></div>
@endsection
