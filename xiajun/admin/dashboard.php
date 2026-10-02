<?php
require __DIR__ . '/inc.php';
require_admin();
$pdo = db();
$stats = [
  'Orders today'      => $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
  'Revenue today'     => money($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at)=CURDATE() AND status<>'cancelled'")->fetchColumn()),
  'Pending orders'    => $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
  'Upcoming bookings' => $pdo->query("SELECT COUNT(*) FROM reservations WHERE rdate>=CURDATE()")->fetchColumn(),
  'Menu dishes'       => $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn(),
  'Messages'          => $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
];
$recent = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 8")->fetchAll();
$top = $pdo->query("SELECT item_name, SUM(qty) q FROM order_items GROUP BY item_name ORDER BY q DESC LIMIT 5")->fetchAll();
admin_head('Dashboard', 'dashboard');
?>
<div class="cards">
  <?php foreach ($stats as $label => $val): ?>
    <div class="stat"><span><?= e($label) ?></span><strong><?= e($val) ?></strong></div>
  <?php endforeach; ?>
</div>
<div class="two">
  <section class="box"><h2>Latest orders</h2>
    <table><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr>
    <?php foreach ($recent as $o): ?>
      <tr><td><a href="orders.php#o<?= (int)$o['id'] ?>"><?= (int)$o['id'] ?></a></td><td><?= e($o['customer_name']) ?></td><td><?= money($o['total']) ?></td><td><span class="tag <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td></tr>
    <?php endforeach; if (!$recent): ?><tr><td colspan="4" class="muted">No orders yet.</td></tr><?php endif; ?>
    </table></section>
  <section class="box"><h2>Best sellers</h2>
    <table><tr><th>Dish</th><th>Sold</th></tr>
    <?php foreach ($top as $t): ?><tr><td><?= e($t['item_name']) ?></td><td><?= (int)$t['q'] ?></td></tr><?php endforeach; if (!$top): ?><tr><td colspan="2" class="muted">Nothing sold yet.</td></tr><?php endif; ?>
    </table></section>
</div>
<?php admin_foot(); ?>
