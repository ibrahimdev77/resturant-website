<?php
$pageTitle = 'Our story';
$active = 'about';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="ghost" aria-hidden="true">故事</div>
  <?php lanterns([['88%',20,60,0,5,'福'],['95%',80,70,.8,5.6,'喜']]); ?>
  <div class="wrap">
    <h1 class="wipe">Our story</h1>
    <p>A family table that outgrew the family.</p>
  </div>
</section>

<section class="sec sec--paper">
  <div class="wrap two-col">
    <div class="timeline stagger">
      <div class="tl"><span class="yr">1998</span><h3>The Sunday table</h3><p>Our grandmother cooks for twenty relatives every Sunday in a two-room flat. Dumpling folding starts at dawn, and nobody is excused.</p></div>
      <div class="tl"><span class="yr">2009</span><h3>A first stall</h3><p>Friends of the family insist on paying for the food. We open a small stall with six dishes and one wok.</p></div>
      <div class="tl"><span class="yr">2016</span><h3>88 Lantern Lane</h3><p>We move into our own dining room, hang the first red lanterns, and put a second wok on the line.</p></div>
      <div class="tl"><span class="yr">Today</span><h3>Online ordering</h3><p>The same recipes now travel to your door in insulated bamboo-lined boxes, still steaming.</p></div>
    </div>
    <div class="story">
      <h2 class="wipe">What we cook, and why</h2>
      <p class="lead-line" style="margin-top:1.2rem">We cook food from several regions of China: Cantonese dim sum, Sichuan heat, Beijing duck, and Jiangnan noodles.</p>
      <p>Each recipe is taught the same way it was taught to us, by standing next to someone who has made it a thousand times. We do not shorten the broth, we do not use shortcuts for the dough, and we do not take dishes off the menu just because they are slow.</p>
    </div>
  </div>
</section>

<section class="sec">
  <div class="wrap">
    <div class="sec-head"><h2 class="wipe">The people at the stove</h2></div>
    <div class="grid chefs stagger">
      <div class="chef"><div class="face">👩‍🍳</div><h3>Chef Mei Xia</h3><p>Head chef. Keeps the dim sum station and the family recipes.</p></div>
      <div class="chef"><div class="face">👨‍🍳</div><h3>Chef Jun Xia</h3><p>Wok master. Runs the fire for the noodles, rice and Sichuan dishes.</p></div>
      <div class="chef"><div class="face">🍵</div><h3>Auntie Lan</h3><p>Tea and sweets. Chooses every leaf that goes into the pot.</p></div>
    </div>
  </div>
</section>

<section class="sec sec--lacquer">
  <div class="wrap">
    <div class="sec-head"><h2 class="wipe">What we hold to</h2></div>
    <div class="grid values stagger">
      <div class="value"><span class="cn">鲜</span><h3>Fresh every morning</h3><p>Dough, fillings and sauces are made the day you eat them.</p></div>
      <div class="value"><span class="cn">火</span><h3>Real wok heat</h3><p>Our woks run hot enough to give the smoky edge called wok hei.</p></div>
      <div class="value"><span class="cn">家</span><h3>Table-for-all hospitality</h3><p>Everyone gets tea, a warm towel and a seat that is never rushed.</p></div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
