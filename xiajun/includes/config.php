<?php
/* ============================================================
   Xiajun – core configuration & helpers
   ============================================================ */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'xiajun_db');
define('DB_USER', 'root');   // XAMPP default
define('DB_PASS', '');       // XAMPP default (empty)
// Auto-detect the URL path of this folder (works even if renamed or nested inside htdocs)
(function () {
    $root = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $proj = str_replace('\\', '/', (string)realpath(__DIR__ . '/..'));
    $base = ($root !== '' && stripos($proj, $root) === 0) ? substr($proj, strlen($root)) : '/xiajun';
    define('BASE_URL', rtrim($base, '/'));
})();
define('SITE_NAME', 'Xiajun');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('<body style="font-family:sans-serif;padding:3rem;background:#1a0b0b;color:#f3ead7">
                <h1>Database not ready</h1>
                <p>Start MySQL in XAMPP, then open
                <a style="color:#d9a441" href="' . BASE_URL . '/install.php">/xiajun/install.php</a> once to create the database.</p></body>');
        }
    }
    return $pdo;
}

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($n): string { return '$' . number_format((float)$n, 2); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_ok(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function take_flash(): ?array {
    if (!empty($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}
function redirect(string $path): void { header('Location: ' . $path); exit; }

/* ---------- Cart (session based, prices always read from DB) ---------- */
function cart_items(): array {
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) return ['items' => [], 'count' => 0, 'total' => 0.0];
    $ids = array_map('intval', array_keys($cart));
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $st  = db()->prepare("SELECT * FROM menu_items WHERE id IN ($in) AND available = 1");
    $st->execute($ids);
    $items = []; $count = 0; $total = 0.0;
    foreach ($st->fetchAll() as $row) {
        $qty = max(1, min(20, (int)$cart[$row['id']]));
        $row['qty'] = $qty;
        $row['line'] = round($row['price'] * $qty, 2);
        $items[] = $row;
        $count += $qty;
        $total += $row['line'];
    }
    return ['items' => $items, 'count' => $count, 'total' => round($total, 2)];
}

function cart_count(): int {
    $n = 0;
    foreach ($_SESSION['cart'] ?? [] as $q) $n += (int)$q;
    return $n;
}

/* ---------- Menu categories ---------- */
function categories(): array {
    return [
        'dimsum'  => ['Dim Sum', '点心'],
        'noodles' => ['Noodles & Rice', '面饭'],
        'mains'   => ['Wok & Mains', '热炒'],
        'seafood' => ['Seafood', '海鲜'],
        'sweets'  => ['Sweets', '甜品'],
        'drinks'  => ['Tea & Drinks', '茶饮'],
    ];
}

define('DELIVERY_FEE', 3.00);
define('FREE_OVER', 40.00);
function delivery_for(float $subtotal): float { return ($subtotal <= 0 || $subtotal >= FREE_OVER) ? 0.0 : DELIVERY_FEE; }

/* Hanging lanterns (decorative) */
function lanterns(array $set): void {
    echo '<div class="lanterns" aria-hidden="true">';
    foreach ($set as $l) {
        printf('<div class="lantern" style="--x:%s;--h:%dpx;--s:%dpx;--d:%.1fs;--t:%.1fs"><i class="cord"></i><div class="body"><i class="cap t"></i>%s<i class="cap b"></i></div><i class="tassel"></i></div>',
            $l[0], $l[1], $l[2], $l[3], $l[4], $l[5] ?? '福');
    }
    echo '</div>';
}

function render_flash(): void {
    if ($f = take_flash()) {
        echo '<div class="alert ' . e($f[0]) . '" role="status">' . e($f[1]) . '</div>';
    }
}
