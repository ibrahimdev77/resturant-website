<?php
require_once __DIR__ . '/../includes/config.php';
define('ADMIN_URL', BASE_URL . '/admin');

function admin_logged_in(): bool { return !empty($_SESSION['admin_id']); }
function require_admin(): void {
    if (!admin_logged_in()) redirect(ADMIN_URL . '/');
}

function admin_head(string $title, string $active): void {
    $links = [
        'dashboard' => ['Dashboard', '/dashboard.php'],
        'orders'    => ['Orders',    '/orders.php'],
        'menu'      => ['Menu items','/menu.php'],
        'inbox'     => ['Inbox',     '/inbox.php'],
    ];
    ?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> · Xiajun admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head><body>
<aside class="side">
  <div class="logo"><span>夏君</span> Xiajun<small>Admin</small></div>
  <nav>
    <?php foreach ($links as $k => [$l, $h]): ?>
      <a href="<?= ADMIN_URL . $h ?>" class="<?= $active === $k ? 'on' : '' ?>"><?= e($l) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="side-foot">
    <div>Signed in as <strong><?= e($_SESSION['admin_user'] ?? '') ?></strong></div>
    <a href="<?= ADMIN_URL ?>/logout.php">Sign out</a>
  </div>
</aside>
<main class="content">
  <h1><?= e($title) ?></h1>
  <?php render_flash(); ?>
<?php }

function admin_foot(): void { echo '</main></body></html>'; }
