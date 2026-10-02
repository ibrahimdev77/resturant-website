<?php
$pageTitle = 'Checkout';
$active = 'cart';
require_once __DIR__ . '/includes/config.php';

$cart = cart_items();
if (!$cart['items']) { flash('error', 'Your cart is empty.'); redirect(BASE_URL . '/cart.php'); }
$fee = delivery_for($cart['total']);
$grand = $cart['total'] + $fee;

$errors = []; $v = ['name'=>'','phone'=>'','address'=>'','notes'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    if (!csrf_ok()) $errors[] = 'Your session expired. Please try again.';
    if ($v['name'] === '' || mb_strlen($v['name']) > 100) $errors[] = 'Enter your name.';
    if (!preg_match('/^[0-9+()\-\s]{6,25}$/', $v['phone'])) $errors[] = 'Enter a valid phone number.';
    if (mb_strlen($v['address']) < 8 || mb_strlen($v['address']) > 255) $errors[] = 'Enter your full delivery address.';
    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO orders (customer_name, phone, address, notes, total) VALUES (?,?,?,?,?)')
                ->execute([$v['name'], $v['phone'], $v['address'], mb_substr($v['notes'], 0, 400), $grand]);
            $oid = (int)$pdo->lastInsertId();
            $ins = $pdo->prepare('INSERT INTO order_items (order_id, item_id, item_name, price, qty) VALUES (?,?,?,?,?)');
            foreach ($cart['items'] as $i) $ins->execute([$oid, $i['id'], $i['name'], $i['price'], $i['qty']]);
            $pdo->commit();
            $_SESSION['cart'] = [];
            $_SESSION['last_order'] = $oid;
            redirect(BASE_URL . '/order-success.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'We could not place your order. Please try again.';
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="ghost" aria-hidden="true">结账</div>
  <div class="wrap"><h1 class="wipe">Checkout</h1><p>Pay cash on delivery. No card details are needed.</p></div>
</section>
<section class="sec" style="padding-top:2rem">
  <div class="wrap cart-layout">
    <div class="panel">
      <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
          <div class="field"><label for="name">Full name</label><input id="name" name="name" value="<?= e($v['name']) ?>" required autocomplete="name"></div>
          <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="<?= e($v['phone']) ?>" required autocomplete="tel"></div>
          <div class="field full"><label for="address">Delivery address</label><input id="address" name="address" value="<?= e($v['address']) ?>" required autocomplete="street-address" placeholder="Street, building, floor"></div>
          <div class="field full"><label for="notes">Notes for the kitchen (optional)</label><textarea id="notes" name="notes" maxlength="400" placeholder="Less spicy, no peanuts, ring the bell twice"><?= e($v['notes']) ?></textarea></div>
        </div>
        <p style="margin-top:1.6rem"><button class="btn btn--solid" type="submit">Place order · <?= money($grand) ?></button></p>
      </form>
    </div>
    <aside class="panel summary">
      <h2 style="font-size:1.7rem;margin-bottom:1rem">Your order</h2>
      <?php foreach ($cart['items'] as $i): ?>
        <div class="mini-order"><span><?= (int)$i['qty'] ?> × <?= e($i['name']) ?></span><span><?= money($i['line']) ?></span></div>
      <?php endforeach; ?>
      <dl>
        <dt>Subtotal</dt><dd><?= money($cart['total']) ?></dd>
        <dt>Delivery</dt><dd><?= $fee == 0 ? 'Free' : money($fee) ?></dd>
        <dt class="grand">Total</dt><dd class="grand"><?= money($grand) ?></dd>
      </dl>
      <p class="note-small"><a href="<?= BASE_URL ?>/cart.php">Edit your cart</a></p>
    </aside>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
