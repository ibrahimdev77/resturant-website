<?php
require_once __DIR__ . '/config.php';
$pageTitle = $pageTitle ?? 'Xiajun';
$active    = $active ?? '';
$cartCount = cart_count();
$nav = [
  'home'    => ['Home',    '/'],
  'menu'    => ['Menu',    '/menu.php'],
  'about'   => ['Our Story','/about.php'],
  'reserve' => ['Reserve', '/reserve.php'],
  'contact' => ['Contact', '/contact.php'],
];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> · Xiajun 夏君</title>
<meta name="description" content="Xiajun 夏君 – hand-pleated dumplings, wok-fired noodles and lacquered duck. Order online or reserve a table.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&family=Ma+Shan+Zheng&family=Marcellus&family=Noto+Serif+SC:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body data-base="<?= BASE_URL ?>" data-page="<?= e($active) ?>" data-csrf="<?= e(csrf_token()) ?>" data-fee="<?= DELIVERY_FEE ?>" data-free="<?= FREE_OVER ?>">

<!-- Red gate: opens once on load, closes on navigation -->
<div class="gate" aria-hidden="true">
  <div class="gate-door left"><span class="studs"></span></div>
  <div class="gate-door right"><span class="studs"></span></div>
  <div class="gate-seal">夏君</div>
</div>

<canvas id="embers" aria-hidden="true"></canvas>

<header class="site-header" id="siteHeader">
  <a class="brand" href="<?= BASE_URL ?>/" aria-label="Xiajun home">
    <span class="brand-seal">夏君</span>
    <span class="brand-name">Xiajun</span>
  </a>
  <nav class="main-nav" id="mainNav" aria-label="Main">
    <?php foreach ($nav as $key => [$label, $href]): ?>
      <a href="<?= BASE_URL . $href ?>" class="<?= $active === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <a class="cart-link" id="cartLink" href="<?= BASE_URL ?>/cart.php" aria-label="Open cart">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 11a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 8z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
    <span class="cart-badge" id="cartBadge" data-count="<?= $cartCount ?>"><?= $cartCount ?></span>
  </a>
  <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false"><span></span><span></span></button>
</header>

<div class="toast-stack" id="toasts" aria-live="polite"></div>
<main>
