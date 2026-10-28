<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบ - ระบบ POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; background: linear-gradient(135deg, #1f2a44, #3b5998); min-height: 100vh; }</style>
</head>
<body class="d-flex align-items-center justify-content-center">
<div class="card shadow-lg border-0" style="width: 400px">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <i class="bi bi-shop fs-1 text-primary"></i>
            <h4 class="mt-2">ระบบ POS</h4>
            <p class="text-muted mb-0">เข้าสู่ระบบเพื่อเริ่มใช้งาน</p>
        </div>
        @if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">อีเมล</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">จดจำฉันไว้</label>
            </div>
            <button class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        </form>
        <div class="alert alert-light border small mt-3 mb-0">
            <b>บัญชีทดลอง</b> (รหัสผ่าน: password)<br>
            ผู้ดูแล: admin@pos.test<br>พนักงานขาย: cashier@pos.test
        </div>
    </div>
</div>
</body>
</html>
