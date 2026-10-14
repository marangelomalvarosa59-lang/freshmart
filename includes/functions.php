<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

/** Sanitize user input to help prevent XSS */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/** Redirect helper */
function redirect($path) {
    header("Location: " . $path);
    exit();
}

/** Check if a user is logged in */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/** Check if the logged-in user is an admin */
function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

/** Force login before accessing a page */
function require_login() {
    if (!is_logged_in()) {
        redirect('/auth/login.php');
    }
}

/** Force admin role before accessing a page */
function require_admin() {
    if (!is_admin()) {
        redirect('/index.php');
    }
}

/** Generate a CSRF token and store it in the session */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Verify a submitted CSRF token */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/** Get number of items currently in the cart */
function cart_count() {
    if (empty($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/** Get cart total price */
function cart_total() {
    if (empty($_SESSION['cart'])) return 0;
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

/** Flash message helpers */
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
