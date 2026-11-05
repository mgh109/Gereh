<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <style>
        :root { color-scheme: light; font-family: Tahoma, Arial, sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f5f5f3; color: #171717; }
        main { width: min(92%, 680px); padding: 48px 32px; background: #fff; border: 1px solid #e8e8e5; border-radius: 20px; text-align: center; }
        h1 { margin: 0 0 12px; font-size: 32px; letter-spacing: 2px; }
        p { color: #666; line-height: 2; margin: 8px 0; }
        .status { display: inline-flex; align-items: center; gap: 8px; margin-top: 20px; padding: 8px 14px; border-radius: 999px; background: #eef8f0; color: #216b36; font-size: 13px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: #26964b; }
    </style>
</head>
<body>
<main>
    <h1>GEREH</h1>
    <p>زیرساخت فروشگاه در حال آماده‌سازی است.</p>
    <p>این صفحه صرفاً برای بررسی راه‌اندازی اولیه CodeIgniter است.</p>
    <span class="status"><span class="dot"></span> برنامه اجرا شده — <?= esc($environment) ?></span>
</main>
</body>
</html>
