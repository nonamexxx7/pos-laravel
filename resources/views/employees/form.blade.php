@extends('layouts.app')
@section('title', $employee->exists ? 'แก้ไขพนักงาน' : 'เพิ่มพนักงานใหม่')
@section('content')
<div class="card" style="max-width:620px"><div class="card-body">
    <form method="POST" action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}">
        @csrf @if ($employee->exists) @method('PUT') @endif
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">ชื่อ-นามสกุล *</label><input name="name" class="form-control" value="{{ old('name', $employee->name) }}" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">อีเมล (ใช้เข้าสู่ระบบ) *</label><input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">รหัสผ่าน {{ $employee->exists ? '(เว้นว่างถ้าไม่เปลี่ยน)' : '*' }}</label><input type="password" name="password" class="form-control" {{ $employee->exists ? '' : 'required' }}></div>
            <div class="col-md-6 mb-3"><label class="form-label">สิทธิ์การใช้งาน *</label>
                <select name="role" class="form-select">
                    <option value="cashier" @selected(old('role', $employee->role) === 'cashier')>พนักงานขาย (Cashier)</option>
                    <option value="admin" @selected(old('role', $employee->role) === 'admin')>ผู้ดูแลระบบ (Admin)</option>
                </select></div>
            <div class="col-md-6 mb-3"><label class="form-label">ตำแหน่ง</label><input name="position" class="form-control" value="{{ old('position', $employee->position) }}"></div>
            <div class="col-md-6 mb-3"><label class="form-label">เบอร์โทร</label><input name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}"></div>
        </div>
        <div class="form-check form-switch mb-3"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="act" @checked(old('is_active', $employee->is_active))><label class="form-check-label" for="act">เปิดใช้งานบัญชี</label></div>
        <button class="btn btn-primary">บันทึก</button>
        <a href="{{ route('employees.index') }}" class="btn btn-light">ยกเลิก</a>
    </form>
</div></div>
@endsection
