<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        body {
            margin:0;
            padding:0;
            font-family: 'Arial', sans-serif;
            background-color:#0d1117; /* خلفية غامقة زي الموقع */
        }
        .container {
            max-width:600px;
            margin:30px auto;
            background:#161b22; /* غامق فاتح */
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 6px 20px rgba(0,0,0,0.4);
            padding:30px;
            color:#e6edf3;
            line-height:1.8;
        }
        .header {
            text-align:center;
            background:#1abc9c;
            color:#fff;
            padding:22px;
            font-size:22px;
            font-weight:bold;
            border-radius:12px 12px 0 0;
        }
        .btn {
            display:inline-block;
            padding:14px 28px;
            margin:30px 0;
            background:linear-gradient(90deg, #00d084, #1abc9c);
            color:#fff !important;
            text-decoration:none;
            border-radius:50px;
            font-size:16px;
            font-weight:bold;
            transition: opacity 0.3s ease;
        }
        .btn:hover {
            opacity:0.9;
        }
        .footer {
            text-align:center;
            color:#8b949e;
            font-size:12px;
            margin-top:30px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">🔒 إعادة تعيين كلمة المرور</div>

        <p>مرحباً <strong>{{ $user->name }}</strong>،</p>
        <p>لقد استلمنا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك على <strong>Taqrer</strong>.</p>
        <p>اضغط على الزر بالأسفل للمتابعة:</p>

        <p style="text-align:center;">
            <a href="{{ $url }}" class="btn">إعادة تعيين كلمة المرور</a>
        </p>

        <p style="font-size:14px; color:#aaa;">
            إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>Taqrer</strong> — جميع الحقوق محفوظة.
        </div>
    </div>

</body>
</html>
