<?php
require __DIR__ . '/inc.php';
unset($_SESSION['admin_id'], $_SESSION['admin_user']);
session_regenerate_id(true);
redirect(ADMIN_URL . '/');
