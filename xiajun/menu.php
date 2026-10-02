<?php
$pageTitle = 'Menu';
$active = 'menu';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/dish.php';

$rows = db()->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY FIELD(category,'dimsum','noodles','mains','seafood','sweets','drinks'), popular DESC, name")->fetchAll();
$byCat = [];
foreach ($rows as $r) $byCat[$r['category']][] = $r;
$cats = categories();
?>
<section class="page-hero">
  <div class="ghost" aria-hidden="true">菜单</div>
  <div class="wrap">
    <h1 class="wipe">The menu</h1>
    <p>Everything is cooked to order. Tap a dish to add it to your cart.</p>
  </div>
</section>

<div class="menu-bar">
  <div class="chips" role="tablist" aria-label="Menu categories">
    <button class="chip active" data-cat="all">All dishes</button>
    <?php foreach ($cats as $key => [$en, $cn]): if (empty($byCat[$key])) continue; ?>
      <button class="chip" data-cat="<?= e($key) ?>"><?= e($en) ?><small><?= e($cn) ?></small></button>
    <?php endforeach; ?>
  </div>
  <div class="menu-tools">
    <label class="toggle"><input type="checkbox" id="spicyOnly"> Spicy only</label>
    <input class="search" id="menuSearch" type="search" placeholder="Search dishes" aria-label="Search dishes">
  </div>
</div>

<section class="sec">
  <div class="wrap">
    <?php foreach ($cats as $key => [$en, $cn]): if (empty($byCat[$key])) continue; ?>
      <div class="menu-group" id="cat-<?= e($key) ?>">
        <h2><?= e($en) ?> <span><?= e($cn) ?></span></h2>
        <div class="grid dishes">
          <?php foreach ($byCat[$key] as $d) render_dish($d); ?>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="empty" id="menuEmpty">
      <h3>No dishes match</h3>
      <p style="margin:.6rem auto 0">Clear the search box or switch off “Spicy only” to see more.</p>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
