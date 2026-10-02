<?php
$pageTitle = 'Reserve a table';
$active = 'reserve';
require __DIR__ . '/includes/header.php';

$errors = []; $v = ['name'=>'','phone'=>'','rdate'=>'','rtime'=>'19:00','guests'=>'2','note'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    if (!csrf_ok()) $errors[] = 'Your session expired. Please try again.';
    if ($v['name'] === '' || mb_strlen($v['name']) > 100) $errors[] = 'Enter your name.';
    if (!preg_match('/^[0-9+()\-\s]{6,25}$/', $v['phone'])) $errors[] = 'Enter a valid phone number.';
    $d = DateTime::createFromFormat('Y-m-d', $v['rdate']);
    if (!$d || $d->format('Y-m-d') !== $v['rdate'] || $v['rdate'] < date('Y-m-d')) $errors[] = 'Choose a date from today onwards.';
    elseif ($d->format('N') == 1) $errors[] = 'We are closed on Mondays. Please choose another day.';
    if (!preg_match('/^\d{2}:\d{2}$/', $v['rtime'])) $errors[] = 'Choose a time.';
    $g = (int)$v['guests'];
    if ($g < 1 || $g > 10) $errors[] = 'We seat 1 to 10 guests. For larger groups, call us.';
    if (!$errors) {
        db()->prepare('INSERT INTO reservations (name, phone, rdate, rtime, guests, note) VALUES (?,?,?,?,?,?)')
            ->execute([$v['name'], $v['phone'], $v['rdate'], $v['rtime'], $g, mb_substr($v['note'], 0, 300)]);
        flash('success', 'Table booked for ' . $g . ' on ' . $d->format('D j M') . ' at ' . $v['rtime'] . '. See you then, ' . $v['name'] . '.');
        redirect(BASE_URL . '/reserve.php');
    }
}
?>
<section class="page-hero">
  <div class="ghost" aria-hidden="true">订座</div>
  <div class="wrap"><h1 class="wipe">Reserve a table</h1><p>Round tables for two to ten. Closed on Mondays.</p></div>
</section>

<section class="sec">
  <div class="wrap two-col">
    <div class="panel">
      <?php render_flash(); ?>
      <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
          <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($v['name']) ?>" required autocomplete="name"></div>
          <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="<?= e($v['phone']) ?>" required autocomplete="tel"></div>
          <div class="field"><label for="rdate">Date</label><input id="rdate" type="date" name="rdate" min="<?= date('Y-m-d') ?>" value="<?= e($v['rdate']) ?>" required></div>
          <div class="field"><label for="rtime">Time</label>
            <select id="rtime" name="rtime">
              <?php for ($m = 11.5 * 60; $m <= 21.5 * 60; $m += 30): $t = sprintf('%02d:%02d', intdiv((int)$m, 60), (int)$m % 60); ?>
                <option value="<?= $t ?>" <?= $v['rtime'] === $t ? 'selected' : '' ?>><?= $t ?></option>
              <?php endfor; ?>
            </select></div>
          <div class="field"><label for="guests">Guests</label>
            <select id="guests" name="guests">
              <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>" <?= (int)$v['guests'] === $i ? 'selected' : '' ?>><?= $i ?> <?= $i === 1 ? 'guest' : 'guests' ?></option><?php endfor; ?>
            </select></div>
          <div class="field full"><label for="note">Anything we should know? (optional)</label><textarea id="note" name="note" maxlength="300" placeholder="Allergies, a birthday, a high chair"><?= e($v['note']) ?></textarea></div>
        </div>
        <p style="margin-top:1.6rem"><button class="btn btn--solid" type="submit">Book this table</button></p>
      </form>
    </div>
    <aside class="info-list">
      <div><h4>Opening hours</h4><p>Tuesday to Thursday 11:30–22:00<br>Friday and Saturday 11:30–23:30<br>Sunday 12:00–21:00</p></div>
      <div><h4>Larger parties</h4><p>For more than ten guests or a private banquet, call +1 (555) 018-8888 and we will plan the menu with you.</p></div>
      <div><h4>Holding your table</h4><p>We keep your table for 15 minutes after your booking time.</p></div>
    </aside>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
