<?php
$pageTitle = 'Dumplings, noodles & lacquered duck';
$active = 'home';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/dish.php';

$featured = db()->query("SELECT * FROM menu_items WHERE popular = 1 AND available = 1 ORDER BY RAND() LIMIT 6")->fetchAll();
$ticker = [['虾饺','Har Gow'],['小笼包','Xiaolongbao'],['担担面','Dan Dan Noodles'],['北京烤鸭','Peking Duck'],['麻婆豆腐','Mapo Tofu'],['叉烧','Char Siu'],['椒盐虾','Salt & Pepper Prawns'],['蛋挞','Egg Tarts']];
?>

<section class="hero">
  <?php lanterns([['7%',70,66,0,4.6,'福'],['20%',150,84,.6,5.4,'喜'],['79%',100,74,1.1,5.0,'福'],['92%',170,92,.3,6.0,'財']]); ?>
  <div class="hero-cn" aria-hidden="true">夏君</div>
  <div class="hero-grid">
    <div class="hero-copy">
      <h1>Steam, fire and patience.</h1>
      <p class="lead">Dumplings pleated by hand every morning, noodles pulled to order, and a wok that has never been allowed to cool. Order for delivery or book a table.</p>
      <div class="hero-actions">
        <a class="btn btn--solid" href="<?= BASE_URL ?>/menu.php">Order now</a>
        <a class="btn" href="<?= BASE_URL ?>/reserve.php">Reserve a table</a>
      </div>
    </div>
    <div class="hero-art" aria-hidden="true">
      <div class="disc"></div>
      <svg viewBox="0 0 500 500" fill="none" stroke-linecap="round">
        <path class="enso" d="M250 22 A228 228 0 1 1 112 62" stroke="#d9a441" stroke-width="6"/>
        <!-- steam -->
        <path class="steam" d="M205 235 C180 200 228 178 202 130" stroke="#f3ead7" stroke-width="6"/>
        <path class="steam s2" d="M255 232 C230 192 278 170 252 118" stroke="#f3ead7" stroke-width="6"/>
        <path class="steam s3" d="M305 235 C282 204 326 182 304 138" stroke="#f3ead7" stroke-width="6"/>
        <!-- bowl -->
        <path d="M108 272 H392 C392 365 332 428 250 428 C168 428 108 365 108 272 Z" fill="#2a0a0c" stroke="#d9a441" stroke-width="4"/>
        <path d="M118 318 H382" stroke="#d9a441" stroke-width="3" stroke-dasharray="3 12"/>
        <path d="M134 352 H366" stroke="#d1301f" stroke-width="3" stroke-dasharray="14 8"/>
        <rect x="212" y="424" width="76" height="16" rx="3" fill="#d9a441"/>
        <ellipse cx="250" cy="272" rx="142" ry="24" fill="#f3ead7"/>
        <ellipse cx="250" cy="274" rx="128" ry="17" fill="#e5ae5c"/>
        <g class="noodle-wave" stroke="#fff3d1" stroke-width="3.5" opacity=".9">
          <path d="M150 272 q18 -12 36 0 t36 0 t36 0 t36 0 t36 0"/>
          <path d="M160 280 q18 -10 36 0 t36 0 t36 0 t36 0"/>
        </g>
        <!-- chopsticks -->
        <path d="M338 96 L268 270" stroke="#c98b4b" stroke-width="8"/>
        <path d="M360 108 L284 272" stroke="#e0a867" stroke-width="8"/>
        <text x="250" y="390" text-anchor="middle" font-family="Ma Shan Zheng, serif" font-size="46" fill="#d9a441" stroke="none">夏君</text>
      </svg>
    </div>
  </div>
  <span class="scroll-hint"></span>
</section>

<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($r = 0; $r < 2; $r++): foreach ($ticker as $t): ?>
      <span><?= e($t[0]) ?><i><?= e($t[1]) ?></i></span><span>✦</span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<section class="sec">
  <div class="wrap">
    <div class="sec-head">
      <h2 class="wipe">Guest favourites</h2>
      <p>The dishes our regulars order before they have even opened the menu.</p>
    </div>
    <div class="grid dishes stagger">
      <?php foreach ($featured as $d) render_dish($d); ?>
    </div>
    <div class="center" style="margin-top:3rem"><a class="btn" href="<?= BASE_URL ?>/menu.php">See the full menu</a></div>
  </div>
</section>

<section class="sec sec--paper">
  <div class="wrap split">
    <div class="vertical-poem wipe" aria-label="When the fire is right, the flavour arrives by itself.">火候到了<br>味道自来</div>
    <div class="story">
      <h2 class="wipe">Cooked the way we learned it at home</h2>
      <p class="lead-line" style="margin-top:1.4rem">Xiajun began as a Sunday table for friends. Now every morning our kitchen folds <span class="num" data-count-to="412">0</span> dumplings by hand, simmers the broth for <span class="num" data-count-to="14">0</span> hours, and cooks from recipes that are <span class="num" data-count-to="3">0</span> generations old.</p>
      <p>Nothing here comes from a packet. The chili oil is steeped in-house, the duck is air-dried for two days, and the noodles are pulled when you order them.</p>
      <p style="margin-top:1.8rem"><a class="btn" style="border-color:var(--vermilion);color:var(--vermilion)" href="<?= BASE_URL ?>/about.php">Read our story</a></p>
    </div>
  </div>
</section>

<section class="sec">
  <div class="wrap">
    <div class="sec-head"><h2 class="wipe">From our wok to your door</h2></div>
    <div class="grid steps stagger">
      <div class="step"><h3>Choose your dishes</h3><p>Browse the menu, filter by spice level, and add what you like.</p></div>
      <div class="step"><h3>Check your cart</h3><p>Adjust quantities, and see your delivery cost update as you go.</p></div>
      <div class="step"><h3>We cook, you eat</h3><p>Your order goes straight to our kitchen and arrives while it is still steaming.</p></div>
    </div>
  </div>
</section>

<section class="sec sec--lacquer cta">
  <div class="wrap">
    <h2 class="wipe">Table for tonight?</h2>
    <p>Round tables seat up to ten. Tell us how many are coming and we will keep the tea hot.</p>
    <a class="btn btn--solid" href="<?= BASE_URL ?>/reserve.php">Reserve a table</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
