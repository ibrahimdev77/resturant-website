<?php
require __DIR__ . '/inc.php';
require_admin();
$pdo = db();
$cats = categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $act = $_POST['action'] ?? '';
    $id  = (int)($_POST['id'] ?? 0);
    if ($act === 'save') {
        $d = [
            trim($_POST['name'] ?? ''), trim($_POST['name_cn'] ?? ''), trim($_POST['description'] ?? ''),
            round((float)($_POST['price'] ?? 0), 2), $_POST['category'] ?? '', trim($_POST['emoji'] ?? '') ?: '🥢',
            max(0, min(3, (int)($_POST['spicy'] ?? 0))), isset($_POST['popular']) ? 1 : 0, isset($_POST['available']) ? 1 : 0,
        ];
        if ($d[0] === '' || $d[3] <= 0 || !isset($cats[$d[4]])) {
            flash('error', 'Name, a price above zero and a category are required.');
        } elseif ($id) {
            $pdo->prepare('UPDATE menu_items SET name=?,name_cn=?,description=?,price=?,category=?,emoji=?,spicy=?,popular=?,available=? WHERE id=?')->execute([...$d, $id]);
            flash('success', 'Saved “' . $d[0] . '”.');
        } else {
            $pdo->prepare('INSERT INTO menu_items (name,name_cn,description,price,category,emoji,spicy,popular,available) VALUES (?,?,?,?,?,?,?,?,?)')->execute($d);
            flash('success', 'Added “' . $d[0] . '” to the menu.');
        }
    } elseif ($act === 'toggle') {
        $pdo->prepare('UPDATE menu_items SET available = 1 - available WHERE id = ?')->execute([$id]);
        flash('success', 'Availability updated.');
    } elseif ($act === 'delete') {
        $pdo->prepare('DELETE FROM menu_items WHERE id = ?')->execute([$id]);
        flash('success', 'Dish deleted.');
    }
    redirect(ADMIN_URL . '/menu.php');
}

$editId = (int)($_GET['edit'] ?? 0);
$edit = ['id'=>0,'name'=>'','name_cn'=>'','description'=>'','price'=>'','category'=>'mains','emoji'=>'🥢','spicy'=>0,'popular'=>0,'available'=>1];
if ($editId) { $st = $pdo->prepare('SELECT * FROM menu_items WHERE id=?'); $st->execute([$editId]); $edit = $st->fetch() ?: $edit; }
$items = $pdo->query("SELECT * FROM menu_items ORDER BY FIELD(category,'dimsum','noodles','mains','seafood','sweets','drinks'), name")->fetchAll();
admin_head('Menu items', 'menu');
?>
<section class="box">
  <h2><?= $edit['id'] ? 'Edit dish' : 'Add a dish' ?></h2>
  <form method="post" class="grid-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
    <label>Name<input name="name" value="<?= e($edit['name']) ?>" required></label>
    <label>Chinese name<input name="name_cn" value="<?= e($edit['name_cn']) ?>"></label>
    <label>Price ($)<input name="price" type="number" step="0.01" min="0.01" value="<?= e($edit['price']) ?>" required></label>
    <label>Category<select name="category"><?php foreach ($cats as $k => [$en]): ?><option value="<?= e($k) ?>" <?= $edit['category'] === $k ? 'selected' : '' ?>><?= e($en) ?></option><?php endforeach; ?></select></label>
    <label>Emoji<input name="emoji" value="<?= e($edit['emoji']) ?>" maxlength="8"></label>
    <label>Spice level<select name="spicy"><?php for ($i = 0; $i <= 3; $i++): ?><option value="<?= $i ?>" <?= (int)$edit['spicy'] === $i ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></label>
    <label class="wide">Description<input name="description" value="<?= e($edit['description']) ?>" maxlength="300"></label>
    <label class="check"><input type="checkbox" name="popular" <?= $edit['popular'] ? 'checked' : '' ?>> Guest favourite</label>
    <label class="check"><input type="checkbox" name="available" <?= $edit['available'] ? 'checked' : '' ?>> Available to order</label>
    <div class="wide row"><button><?= $edit['id'] ? 'Save changes' : 'Add dish' ?></button><?php if ($edit['id']): ?><a class="btn-link" href="menu.php">Cancel</a><?php endif; ?></div>
  </form>
</section>

<section class="box">
  <h2>All dishes (<?= count($items) ?>)</h2>
  <div class="scroll"><table>
    <tr><th></th><th>Dish</th><th>Category</th><th>Price</th><th>Status</th><th></th></tr>
    <?php foreach ($items as $i): ?>
      <tr class="<?= $i['available'] ? '' : 'off' ?>">
        <td class="em"><?= e($i['emoji']) ?></td>
        <td><strong><?= e($i['name']) ?></strong> <span class="muted"><?= e($i['name_cn']) ?></span><?= $i['popular'] ? ' <span class="tag preparing">favourite</span>' : '' ?></td>
        <td><?= e($cats[$i['category']][0] ?? $i['category']) ?></td>
        <td><?= money($i['price']) ?></td>
        <td><?= $i['available'] ? 'On menu' : 'Hidden' ?></td>
        <td class="acts">
          <a class="btn-link" href="?edit=<?= (int)$i['id'] ?>">Edit</a>
          <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$i['id'] ?>"><button class="ghost"><?= $i['available'] ? 'Hide' : 'Show' ?></button></form>
          <form method="post" class="inline" onsubmit="return confirm('Delete this dish permanently?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$i['id'] ?>"><button class="danger">Delete</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php admin_foot(); ?>
