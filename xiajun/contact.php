<?php
$pageTitle = 'Contact';
$active = 'contact';
require __DIR__ . '/includes/header.php';

$errors = []; $v = ['name'=>'','email'=>'','message'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) $v[$k] = trim((string)($_POST[$k] ?? ''));
    if (!csrf_ok()) $errors[] = 'Your session expired. Please try again.';
    if ($v['name'] === '' || mb_strlen($v['name']) > 100) $errors[] = 'Enter your name.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (mb_strlen($v['message']) < 5 || mb_strlen($v['message']) > 2000) $errors[] = 'Write a message between 5 and 2000 characters.';
    if (!$errors) {
        db()->prepare('INSERT INTO messages (name, email, message) VALUES (?,?,?)')->execute([$v['name'], $v['email'], $v['message']]);
        flash('success', 'Thank you, ' . $v['name'] . '. We will reply within a day.');
        redirect(BASE_URL . '/contact.php');
    }
}
?>
<section class="page-hero">
  <div class="ghost" aria-hidden="true">联系</div>
  <div class="wrap"><h1 class="wipe">Say hello</h1><p>Questions, feedback or catering enquiries are all welcome.</p></div>
</section>

<section class="sec">
  <div class="wrap two-col">
    <div class="panel">
      <?php render_flash(); ?>
      <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
          <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($v['name']) ?>" required></div>
          <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($v['email']) ?>" required></div>
          <div class="field full"><label for="message">Message</label><textarea id="message" name="message" required><?= e($v['message']) ?></textarea></div>
        </div>
        <p style="margin-top:1.6rem"><button class="btn btn--solid" type="submit">Send message</button></p>
      </form>
    </div>
    <aside class="info-list">
      <div><h4>Visit</h4><p>88 Lantern Lane, Chinatown Quarter</p></div>
      <div><h4>Call</h4><p>+1 (555) 018-8888</p></div>
      <div><h4>Email</h4><p>hello@xiajun.example</p></div>
      <div><h4>Delivery area</h4><p>We deliver within 5 km of the restaurant. Free delivery on orders over $<?= number_format(FREE_OVER, 0) ?>.</p></div>
    </aside>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
