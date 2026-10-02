<?php
/* Hidden login – reachable only by typing /xiajun/admin in the address bar. */
require __DIR__ . '/inc.php';
if (admin_logged_in()) redirect(ADMIN_URL . '/dashboard.php');

$error = '';
$now = time();
$locked = ($_SESSION['lock_until'] ?? 0) > $now;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($locked) {
        $error = 'Too many attempts. Wait ' . ($_SESSION['lock_until'] - $now) . ' seconds and try again.';
    } elseif (!csrf_ok()) {
        $error = 'Session expired. Refresh and try again.';
    } else {
        $u = trim((string)($_POST['username'] ?? ''));
        $p = (string)($_POST['password'] ?? '');
        $st = db()->prepare('SELECT * FROM admins WHERE username = ?');
        $st->execute([$u]);
        $row = $st->fetch();
        if ($row && password_verify($p, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$row['id'];
            $_SESSION['admin_user'] = $row['username'];
            $_SESSION['fails'] = 0;
            redirect(ADMIN_URL . '/dashboard.php');
        }
        $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
        if ($_SESSION['fails'] >= 5) { $_SESSION['lock_until'] = $now + 60; $_SESSION['fails'] = 0; }
        usleep(400000);
        $error = 'Wrong username or password.';
    }
}
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Staff sign in · Xiajun</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head><body class="login-body">
<form class="login" method="post" autocomplete="off">
  <div class="seal">夏君</div>
  <h1>Staff sign in</h1>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username<input name="username" required autofocus></label>
  <label>Password<input name="password" type="password" required></label>
  <button type="submit">Sign in</button>
</form>
</body></html>
