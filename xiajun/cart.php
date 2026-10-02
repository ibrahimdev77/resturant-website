<?php
$pageTitle = 'Your cart';
$active = 'cart';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="ghost" aria-hidden="true">购物</div>
  <div class="wrap"><h1 class="wipe">Your cart</h1><p>Review your dishes before checkout.</p></div>
</section>
<section class="sec" style="padding-top:2rem">
  <div class="wrap">
    <div id="cartRoot"><p class="note-small">Loading your cart…</p></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
