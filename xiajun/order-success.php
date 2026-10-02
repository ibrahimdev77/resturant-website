<?php
$pageTitle = 'Order placed';
$active = 'cart';
require_once __DIR__ . '/includes/config.php';
$oid = (int)($_SESSION['last_order'] ?? 0);
if (!$oid) redirect(BASE_URL . '/menu.php');
$st = db()->prepare('SELECT * FROM orders WHERE id = ?'); $st->execute([$oid]); $order = $st->fetch();
if (!$order) redirect(BASE_URL . '/menu.php');
$st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?'); $st->execute([$oid]); $items = $st->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="sec" style="padding-top:9rem">
  <?php lanterns([['10%',40,70,0,5,'福'],['88%',60,76,.7,5.4,'喜']]); ?>
  <div class="wrap success-card">
    <svg class="tick" viewBox="0 0 120 120" fill="none" stroke="#d9a441" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="60" cy="60" r="54"/><path d="M36 62 L54 80 L86 42"/>
    </svg>
    <h1 style="font-size:clamp(2.2rem,5vw,3.4rem)">Thank you, <?= e($order['customer_name']) ?></h1>
    <p class="order-no">Order #<?= str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT) ?></p>
    <div class="panel" style="text-align:left">
      <?php foreach ($items as $i): ?>
        <div class="mini-order"><span><?= (int)$i['qty'] ?> × <?= e($i['item_name']) ?></span><span><?= money($i['price'] * $i['qty']) ?></span></div>
      <?php endforeach; ?>
      <div class="mini-order" style="border:0;font-family:var(--font-display);font-size:1.3rem;color:var(--gold-soft);margin-top:.6rem"><span>Total (cash on delivery)</span><span><?= money($order['total']) ?></span></div>
      <p class="note-small" style="margin-top:1rem">Delivering to: <?= e($order['address']) ?></p>
    </div>
    <p style="margin:2rem auto 1.6rem">Your dishes are going to the wok now. Expect them in about 35 to 45 minutes.</p>
    <a class="btn btn--solid" href="<?= BASE_URL ?>/menu.php">Order something else</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
