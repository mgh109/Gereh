<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود مدیریت | گره</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f5f7;color:#202124;font-family:Tahoma,Arial,sans-serif;padding:24px}
        .panel{width:min(100%,420px);background:#fff;border:1px solid #e7e8eb;border-radius:18px;padding:32px;box-shadow:0 16px 50px #1c24300d}
        h1{font-size:22px;margin:0 0 8px}.muted{color:#777;font-size:13px;margin:0 0 26px}
        label{display:block;font-size:13px;margin:16px 0 8px}input{width:100%;height:46px;border:1px solid #ddd;border-radius:9px;padding:0 12px;font:inherit}
        button{width:100%;height:46px;border:0;border-radius:9px;background:#202124;color:#fff;font:inherit;margin-top:22px;cursor:pointer}
        .error{background:#fff0f0;color:#a22;padding:10px;border-radius:8px;font-size:13px;margin-bottom:12px}
    </style>
</head>
<body>
<main class="panel">
    <h1>ورود به پنل مدیریت گره</h1>
    <p class="muted">برای مدیریت فروشگاه وارد حساب مدیریتی خود شوید.</p>
    <?php if (session()->getFlashdata('error')): ?><div class="error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?><div class="error">لطفاً اطلاعات واردشده را بررسی کنید.</div><?php endif; ?>
    <form method="post" action="<?= site_url('admin/login') ?>">
        <?= csrf_field() ?>
        <label for="email">ایمیل</label>
        <input id="email" name="email" type="email" autocomplete="username" required value="<?= old('email') ?>">
        <label for="password">رمز عبور</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit">ورود به پنل</button>
    </form>
</main>
</body>
</html>