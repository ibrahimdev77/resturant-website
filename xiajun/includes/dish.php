<?php
/* Renders one dish card */
function render_dish(array $d): void {
    $search = strtolower($d['name'] . ' ' . $d['name_cn'] . ' ' . $d['description']);
    ?>
    <article class="dish" data-cat="<?= e($d['category']) ?>" data-spicy="<?= (int)$d['spicy'] ?>" data-search="<?= e($search) ?>">
      <?php if ($d['popular']): ?><span class="stamp" title="Guest favourite">人气</span><?php endif; ?>
      <div class="dish-art"><span><?= e($d['emoji']) ?></span></div>
      <h3><?= e($d['name']) ?></h3>
      <div class="cn"><?= e($d['name_cn']) ?>
        <?php if ($d['spicy']): ?><span class="chilies" title="Spice level <?= (int)$d['spicy'] ?> of 3"><?= str_repeat('🌶', (int)$d['spicy']) ?></span><?php endif; ?>
      </div>
      <p><?= e($d['description']) ?></p>
      <div class="dish-foot">
        <span class="price"><?= money($d['price']) ?></span>
        <button class="btn btn--small" data-add="<?= (int)$d['id'] ?>" aria-label="Add <?= e($d['name']) ?> to cart">Add to cart</button>
      </div>
    </article>
    <?php
}
