<?php
/* JSON cart API – session based. Prices are always read from the database. */
require __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

function out(array $extra = []): void {
    $c = cart_items();
    echo json_encode(array_merge([
        'ok' => true, 'count' => $c['count'], 'total' => $c['total'],
        'items' => array_map(fn($i) => [
            'id' => (int)$i['id'], 'name' => $i['name'], 'name_cn' => $i['name_cn'],
            'emoji' => $i['emoji'], 'price' => (float)$i['price'],
            'qty' => $i['qty'], 'line' => $i['line'],
        ], $c['items']),
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') out();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }

$in = json_decode(file_get_contents('php://input'), true) ?: [];
if (!isset($in['csrf']) || !hash_equals(csrf_token(), (string)$in['csrf'])) {
    http_response_code(403); echo json_encode(['ok' => false, 'error' => 'Session expired. Refresh the page.']); exit;
}

$action = $in['action'] ?? '';
$id     = (int)($in['id'] ?? 0);
$cart   = $_SESSION['cart'] ?? [];

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    out();
}

if ($id > 0) {
    $st = db()->prepare('SELECT id, name FROM menu_items WHERE id = ? AND available = 1');
    $st->execute([$id]);
    $dish = $st->fetch();
    if (!$dish && $action !== 'remove') {
        http_response_code(404); echo json_encode(['ok' => false, 'error' => 'That dish is not available.']); exit;
    }
    switch ($action) {
        case 'add':
            $cart[$id] = min(20, ($cart[$id] ?? 0) + 1);
            break;
        case 'set':
            $q = (int)($in['qty'] ?? 1);
            if ($q <= 0) unset($cart[$id]); else $cart[$id] = min(20, $q);
            break;
        case 'remove':
            unset($cart[$id]);
            break;
    }
    $_SESSION['cart'] = $cart;
    out(['added' => $dish['name'] ?? null]);
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Bad request.']);
