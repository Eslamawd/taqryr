<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>فاتورة الإعلان</title>
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
      width:100%;
    }
    .container {
      max-width:600px;
    }
    .header {
      background:#1abc9c;
      color:#fff;
      font-size:20px;
      font-weight:bold;
      padding:20px;
      text-align:center;
    }
    .content {
      padding:30px;
      color:#e6edf3;
      line-height:1.8;
      font-size:15px;
      background:#161b22;
    }
    .invoice-table {
      width:100%;
      border-collapse:collapse;
      margin-top:15px;
    }
    .invoice-table th,
    .invoice-table td {
      border:1px solid #2d333b;
      padding:10px;
      text-align:center;
      color:#e6edf3;
    }
    .invoice-table th {
      background:#1abc9c;
      color:#fff;
    }
    .footer {
      background:#0d1117;
      text-align:center;
      padding:15px;
      font-size:12px;
      color:#8b949e;
      border-top:1px solid #2d333b;
    }
  </style>
</head>
<body>
  <table align="center" cellpadding="0" cellspacing="0" class="container">
    <tr>
      <td class="header">
        فاتورة خصم رصيد الإعلان
      </td>
    </tr>

    <tr>
      <td class="content">
        <p style="color:#e6edf3; margin:0 0 10px 0;">
  مرحباً <strong style="color:#fff;">{{ $ad->user->name }}</strong> 👋
</p>
<p style="color:#e6edf3; margin:0 0 10px 0;">
  تم خصم الرصيد بنجاح للإعلان التالي:
</p>

        <table class="invoice-table">
          <tr>
            <th>اسم الإعلان</th>
            <td>{{ $ad->name }}</td>
          </tr>
          <tr>
            <th>المبلغ المخصوم</th>
            <td>{{ number_format($ad->budget, 2) }} SAR</td>
          </tr>
          <tr>
            <th>الرصيد بعد الخصم</th>
            <td>{{ number_format($ad->user->balanceInt / 100, 2) }} SAR </td>
          </tr>
          <tr>
            <th>تاريخ الخصم</th>
            <td>{{ now()->format('Y-m-d H:i') }}</td>
          </tr>
          <tr>
            <th>تاريخ نهاية الإعلان</th>
            <td>{{ optional($ad->end_date)->format('Y-m-d H:i') }}</td>
          </tr>
        </table>

        <p style="margin-top:20px;">شكراً لاستخدامك <strong>Taqrer</strong> 🚀</p>
      </td>
    </tr>

    <tr>
      <td class="footer">
        &copy; {{ date('Y') }} <strong>Taqrer</strong>. جميع الحقوق محفوظة.
      </td>
    </tr>
  </table>
</body>
</html>
