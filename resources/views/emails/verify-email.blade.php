<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تأكيد البريد الإلكتروني</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin:0; 
      padding:0; 
      font-family:Arial, sans-serif; 
      background-color:#0d1117; 
      direction:rtl;
    }
    table {
      border-collapse:collapse;
    }
    .container {
      width:100%; 
      max-width:600px;
    }
    .btn {
      display:inline-block; 
      padding:14px 28px; 
      background:#1abc9c; 
      color:#fff !important; 
      text-decoration:none; 
      border-radius:50px; 
      font-size:16px; 
      font-weight:bold;
    }
    @media only screen and (max-width:620px) {
      .container {
        width:100% !important; 
      }
      .btn {
        display:block !important;
        width:100% !important;
        text-align:center !important;
      }
      .padding-mobile {
        padding:20px !important;
      }
    }
  </style>
</head>
<body>

  <table align="center" width="100%" cellpadding="0" cellspacing="0" bgcolor="#0d1117" style="padding:20px 0;">
    <tr>
      <td align="center">
        <table class="container" width="600" cellpadding="0" cellspacing="0" bgcolor="#161b22" style="border-radius:16px; overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td bgcolor="#1abc9c" align="center" style="padding:22px; font-size:22px; font-weight:bold; color:#ffffff; border-radius:16px 16px 0 0;">
              مرحباً {{ $user->name }} 👋
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td class="padding-mobile" style="padding:30px; color:#e6edf3; line-height:1.8; font-size:15px;">
              <p style="margin:0 0 15px 0;">شكراً لانضمامك إلى <strong>Taqrer</strong>.</p>
              <p style="margin:0 0 25px 0;">من فضلك اضغط على الزر بالأسفل لتأكيد بريدك الإلكتروني وتفعيل حسابك:</p>

              <div style="text-align:center; margin:30px 0;">
                <a href="{{ $url }}" class="btn">✅ تأكيد البريد الإلكتروني</a>
              </div>

              <p style="font-size:14px; color:#aaaaaa; margin-top:25px;">
                إذا لم تقم بإنشاء حساب، يمكنك تجاهل هذه الرسالة.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td bgcolor="#0d1117" align="center" style="padding:20px; color:#8b949e; font-size:12px; border-top:1px solid #2d333b;">
              &copy; {{ date('Y') }} <strong>Taqrer</strong>. جميع الحقوق محفوظة.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
