<?php
require __DIR__ . '/inc.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'del_msg') { $pdo->prepare('DELETE FROM messages WHERE id=?')->execute([$id]); flash('success', 'Message deleted.'); }
    if (($_POST['action'] ?? '') === 'del_res') { $pdo->prepare('DELETE FROM reservations WHERE id=?')->execute([$id]); flash('success', 'Reservation removed.'); }
    redirect(ADMIN_URL . '/inbox.php');
}
$res  = $pdo->query('SELECT * FROM reservations ORDER BY rdate, rtime')->fetchAll();
$msgs = $pdo->query('SELECT * FROM messages ORDER BY id DESC')->fetchAll();
admin_head('Inbox', 'inbox');
?>
<section class="box">
  <h2>Reservations (<?= count($res) ?>)</h2>
  <div class="scroll"><table>
    <tr><th>Date</th><th>Time</th><th>Guests</th><th>Name</th><th>Phone</th><th>Note</th><th></th></tr>
    <?php foreach ($res as $r): ?>
      <tr class="<?= $r['rdate'] < date('Y-m-d') ? 'off' : '' ?>">
        <td><?= e($r['rdate']) ?></td><td><?= e($r['rtime']) ?></td><td><?= (int)$r['guests'] ?></td>
        <td><?= e($r['name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['note']) ?></td>
        <td><form method="post" class="inline" onsubmit="return confirm('Remove this reservation?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_res"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="danger">Remove</button></form></td>
      </tr>
    <?php endforeach; if (!$res): ?><tr><td colspan="7" class="muted">No reservations yet.</td></tr><?php endif; ?>
  </table></div>
</section>

<section class="box">
  <h2>Messages (<?= count($msgs) ?>)</h2>
  <?php foreach ($msgs as $m): ?>
    <article class="msg">
      <header><strong><?= e($m['name']) ?></strong> · <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a> <span class="muted"><?= e($m['created_at']) ?></span></header>
      <p><?= nl2br(e($m['message'])) ?></p>
      <form method="post" class="inline" onsubmit="return confirm('Delete this message?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_msg"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="danger">Delete</button></form>
    </article>
  <?php endforeach; if (!$msgs): ?><p class="muted">No messages yet.</p><?php endif; ?>
</section>
<?php admin_foot(); ?>
