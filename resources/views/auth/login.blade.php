<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - Qat ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: linear-gradient(135deg,#1B5E20 0%,#2E7D32 100%); min-height:100vh; display:flex;align-items:center;justify-content:center; }
        .login-card { background:#fff; border-radius:20px; padding:48px 32px; max-width:420px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); }
        .brand { text-align:center; margin-bottom:32px; }
        .brand i { font-size:64px; color:#1B5E20; }
        .brand h1 { font-size:24px; color:#1B5E20; margin:8px 0 0; font-weight:700; }
        .brand p { color:#666; margin:0; }
        .form-control { padding:14px 16px; border-radius:12px; border:2px solid #e0e0e0; }
        .form-control:focus { border-color:#1B5E20; box-shadow:none; }
        .btn-primary { background:#1B5E20; border:none; padding:14px; border-radius:12px; font-weight:600; }
        .btn-primary:hover { background:#2E7D32; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <i class="bi bi-leaf-fill"></i>
            <h1>Qat ERP</h1>
            <p>نظام تاجر القات</p>
        </div>
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">اسم المستخدم</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
            </button>
        </form>
    </div>
</body>
</html>
