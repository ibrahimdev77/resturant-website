<?php
/* ============================================================
   Xiajun – one-click installer.
   Open http://localhost/xiajun/install.php ONCE, then delete this file.
   ============================================================ */
require __DIR__ . '/includes/config.php';

$log = []; $ok = true;
try {
    $root = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $root->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $log[] = 'Database “' . DB_NAME . '” is ready.';
    $pdo = db();

    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(60) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        name_cn VARCHAR(60) DEFAULT '',
        description VARCHAR(300) DEFAULT '',
        price DECIMAL(8,2) NOT NULL,
        category VARCHAR(30) NOT NULL,
        emoji VARCHAR(16) DEFAULT '🥢',
        spicy TINYINT DEFAULT 0,
        popular TINYINT DEFAULT 0,
        available TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        phone VARCHAR(40) NOT NULL,
        address VARCHAR(255) NOT NULL,
        notes VARCHAR(400) DEFAULT '',
        total DECIMAL(10,2) NOT NULL,
        status ENUM('pending','preparing','delivered','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        item_id INT NULL,
        item_name VARCHAR(120) NOT NULL,
        price DECIMAL(8,2) NOT NULL,
        qty INT NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(40) NOT NULL,
        rdate DATE NOT NULL,
        rtime VARCHAR(10) NOT NULL,
        guests INT NOT NULL,
        note VARCHAR(300) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $log[] = 'Tables created.';

    // Admin account
    $st = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE username = ?');
    $st->execute(['ibrahim']);
    if (!$st->fetchColumn()) {
        $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute(['ibrahim', password_hash('12345678', PASSWORD_DEFAULT)]);
        $log[] = 'Admin account “ibrahim” created.';
    } else {
        $log[] = 'Admin account already exists – left untouched.';
    }

    // Menu seed
    if (!(int)$pdo->query('SELECT COUNT(*) FROM menu_items')->fetchColumn()) {
        $menu = [
          ['Har Gow','虾饺','Translucent crystal dumplings stuffed with plump river shrimp and bamboo shoots.',7.50,'dimsum','🥟',0,1],
          ['Xiaolongbao','小笼包','Soup-filled pork dumplings, pleated by hand and steamed in bamboo.',8.50,'dimsum','🥟',0,1],
          ['Pan-Fried Potstickers','锅贴','Crisp-bottomed pork and chive dumplings with a black vinegar dip.',7.00,'dimsum','🥟',0,0],
          ['Crispy Spring Rolls','春卷','Cabbage, mushroom and carrot in a shatter-crisp wrapper.',5.50,'dimsum','🥢',0,0],
          ['Wontons in Chili Oil','红油抄手','Silky pork wontons in Sichuan red oil, garlic and sesame.',8.00,'dimsum','🌶️',2,1],
          ['Dan Dan Noodles','担担面','Sesame-chili sauce, minced pork, preserved greens and numbing peppercorn.',11.50,'noodles','🍜',3,1],
          ['Beef Brisket Noodle Soup','牛腩面','Slow-braised brisket in a star anise broth with hand-pulled noodles.',13.00,'noodles','🍜',1,0],
          ['Yangzhou Fried Rice','扬州炒饭','Wok-hei rice with shrimp, char siu, egg and spring onion.',10.50,'noodles','🍚',0,1],
          ['Wok-Fried Chow Mein','炒面','Egg noodles tossed with bean sprouts, chives and soy.',10.00,'noodles','🍜',0,0],
          ['Singapore Rice Noodles','星洲炒米','Curry-dusted vermicelli with shrimp, egg and peppers.',11.00,'noodles','🍜',1,0],
          ['Kung Pao Chicken','宫保鸡丁','Diced chicken, roasted peanuts, dried chilies and Sichuan pepper.',14.50,'mains','🍗',2,1],
          ['Mapo Tofu','麻婆豆腐','Silken tofu in fiery doubanjiang sauce with minced beef.',12.50,'mains','🌶️',3,0],
          ['Peking Duck (Half)','北京烤鸭','Lacquered duck with crisp skin, thin pancakes, hoisin and cucumber.',32.00,'mains','🦆',0,1],
          ['Sweet & Sour Pork','咕噜肉','Crisp pork in a bright pineapple and tomato glaze.',13.50,'mains','🍖',0,0],
          ['Char Siu','叉烧','Honey-glazed barbecued pork shoulder, sliced thick.',15.00,'mains','🍖',0,0],
          ['Garlic Bok Choy','蒜蓉白菜','Blistered bok choy with garlic and a splash of Shaoxing wine.',8.50,'mains','🥬',0,0],
          ['Salt & Pepper Prawns','椒盐虾','Shell-on prawns flash-fried with chili, garlic and sea salt.',17.50,'seafood','🦐',1,1],
          ['Steamed Sea Bass','清蒸鲈鱼','Whole bass steamed with ginger, spring onion and hot soy oil.',24.00,'seafood','🐟',0,0],
          ['Black Bean Clams','豉椒炒蚬','Clams wok-tossed with fermented black bean and chili.',16.00,'seafood','🦪',1,0],
          ['Mango Pomelo Sago','杨枝甘露','Chilled mango, pomelo and sago pearls in coconut cream.',7.00,'sweets','🥭',0,0],
          ['Black Sesame Tangyuan','芝麻汤圆','Glutinous rice balls with black sesame in ginger syrup.',6.50,'sweets','🍡',0,1],
          ['Egg Tarts','蛋挞','Flaky pastry with warm, silky custard.',6.00,'sweets','🥧',0,0],
          ['Jasmine Tea Pot','茉莉花茶','Whole-leaf jasmine, served by the pot for two.',5.00,'drinks','🍵',0,0],
          ['Chrysanthemum Honey Tea','菊花茶','Dried blossoms steeped with rock sugar.',4.50,'drinks','🌼',0,0],
          ['Lychee Soda','荔枝汽水','Sparkling lychee with a squeeze of lime.',4.50,'drinks','🥤',0,0],
        ];
        $ins = $pdo->prepare('INSERT INTO menu_items (name,name_cn,description,price,category,emoji,spicy,popular) VALUES (?,?,?,?,?,?,?,?)');
        foreach ($menu as $m) $ins->execute($m);
        $log[] = count($menu) . ' menu dishes added.';
    } else {
        $log[] = 'Menu already has dishes – skipped seeding.';
    }
} catch (Throwable $e) {
    $ok = false;
    $log[] = 'Error: ' . $e->getMessage();
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Xiajun installer</title>
<style>
body{margin:0;min-height:100vh;display:grid;place-items:center;background:#1a0b0b;color:#f3ead7;font-family:system-ui,sans-serif}
.box{max-width:520px;padding:2.5rem;border:1px solid #d9a441;border-radius:4px;background:#2a1010}
h1{font-weight:400;margin-top:0;color:#d9a441}li{margin:.4rem 0}a{color:#d9a441}
.bad{color:#ff8a7a}
</style></head><body><div class="box">
<h1><?= $ok ? 'Xiajun is installed' : 'Install problem' ?></h1>
<ul><?php foreach ($log as $l): ?><li class="<?= str_starts_with($l,'Error') ? 'bad' : '' ?>"><?= e($l) ?></li><?php endforeach; ?></ul>
<?php if ($ok): ?>
<p>Now <strong>delete install.php</strong> from the xiajun folder.</p>
<p><a href="<?= BASE_URL ?>/">Open the website</a></p>
<?php else: ?>
<p>Make sure MySQL is running in the XAMPP Control Panel, then refresh this page.</p>
<?php endif; ?>
</div></body></html>
