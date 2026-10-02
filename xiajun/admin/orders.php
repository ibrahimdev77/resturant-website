<?php
require __DIR__ . '/inc.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'status' && in_array($_POST['status'] ?? '', ['pending','preparing','delivered','cancelled'], true)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$_POST['status'], $id]);
        flash('success', 'Order #' . $id . ' marked as ' . $_POST['status'] . '.');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM orders WHERE id = ?')->execute([$id]);
        flash('success', 'Order #' . $id . ' deleted.');
    }
    redirect(ADMIN_URL . '/orders.php');
}

$filter = $_GET['status'] ?? '';
$sql = 'SELECT * FROM orders' . ($filter ? ' WHERE status = ?' : '') . ' ORDER BY id DESC LIMIT 100';
$st = $pdo->prepare($sql); $st->execute($filter ? [$filter] : []);
$orders = $st->fetchAll();
$itemsBy = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $q = $pdo->query('SELECT * FROM order_items WHERE order_id IN (' . implode(',', array_map('intval', $ids)) . ')');
    foreach ($q as $r) $itemsBy[$r['order_id']][] = $r;
}
admin_head('Orders', 'orders');
?>
<p class="filters">
  <?php foreach (['' => 'All', 'pending' => 'Pending', 'preparing' => 'Preparing', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $k => $l): ?>
    <a class="<?= $filter === $k ? 'on' : '' ?>" href="?status=<?= $k ?>"><?= $l ?></a>
  <?php endforeach; ?>
</p>
<?php foreach ($orders as $o): ?>
  <section class="box order" id="o<?= (int)$o['id'] ?>">
    <header>
      <div><strong>Order #<?= (int)$o['id'] ?></strong> <span class="muted"><?= e($o['created_at']) ?></span></div>
      <span class="tag <?= e($o['status']) ?>"><?= e($o['status']) ?></span>
    </header>
    <p><strong><?= e($o['customer_name']) ?></strong> · <?= e($o['phone']) ?><br><?= e($o['address']) ?></p>
    <?php if ($o['notes']): ?><p class="note">Note: <?= e($o['notes']) ?></p><?php endif; ?>
    <table>
      <?php foreach ($itemsBy[$o['id']] ?? [] as $i): ?>
        <tr><td><?= (int)$i['qty'] ?> × <?= e($i['item_name']) ?></td><td class="r"><?= money($i['price'] * $i['qty']) ?></td></tr>
      <?php endforeach; ?>
      <tr><td><strong>Total</strong></td><td class="r"><strong><?= money($o['total']) ?></strong></td></tr>
    </table>
    <div class="row">
      <form method="post" class="inline">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="action" value="status">
        <select name="status">
          <?php foreach (['pending','preparing','delivered','cancelled'] as $s): ?><option <?= $o['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
        <button>Update status</button>
      </form>
      <form method="post" class="inline" onsubmit="return confirm('Delete order #<?= (int)$o['id'] ?> permanently?')">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="action" value="delete">
        <button class="danger">Delete</button>
      </form>
    </div>
  </section>
<?php endforeach; if (!$orders): ?><p class="muted">No orders found.</p><?php endif; ?>
<?php admin_foot(); ?>
