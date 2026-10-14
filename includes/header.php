<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? sanitize($page_title) . ' - FreshMart' : 'FreshMart Grocery Store'; ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/css/style.css">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            DEFAULT: '#16a34a',
            dark: '#15803d',
            light: '#dcfce7'
          }
        }
      }
    }
  }
</script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<nav class="bg-white shadow sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center h-16">
      <a href="/index.php" class="flex items-center gap-2 text-2xl font-bold text-brand">
        🛒 FreshMart
      </a>

      <button id="mobile-menu-btn" class="md:hidden text-2xl">☰</button>

      <div class="hidden md:flex items-center gap-6" id="nav-links">
        <a href="/index.php" class="hover:text-brand transition">Home</a>
        <a href="/cart.php" class="relative hover:text-brand transition">
          Cart
          <span id="cart-count" class="absolute -top-2 -right-4 bg-brand text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo cart_count(); ?></span>
        </a>
        <?php if (is_logged_in()): ?>
          <?php if (is_admin()): ?>
            <a href="/admin/index.php" class="hover:text-brand transition">Admin Panel</a>
          <?php endif; ?>
          <a href="/orders.php" class="hover:text-brand transition">My Orders</a>
          <span class="text-sm text-gray-500">Hi, <?php echo sanitize($_SESSION['full_name']); ?></span>
          <a href="/auth/logout.php" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition">Logout</a>
        <?php else: ?>
          <a href="/auth/login.php" class="hover:text-brand transition">Login</a>
          <a href="/auth/register.php" class="bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-dark transition">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>

    <div id="nav-links-mobile" class="hidden md:hidden flex-col gap-3 pb-4">
      <a href="/index.php" class="block py-1">Home</a>
      <a href="/cart.php" class="block py-1">Cart (<?php echo cart_count(); ?>)</a>
      <?php if (is_logged_in()): ?>
        <?php if (is_admin()): ?>
          <a href="/admin/index.php" class="block py-1">Admin Panel</a>
        <?php endif; ?>
        <a href="/orders.php" class="block py-1">My Orders</a>
        <a href="/auth/logout.php" class="block py-1">Logout</a>
      <?php else: ?>
        <a href="/auth/login.php" class="block py-1">Login</a>
        <a href="/auth/register.php" class="block py-1">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="flex-grow max-w-6xl mx-auto w-full px-4 py-8">
<?php $flash = get_flash(); if ($flash): ?>
  <div class="mb-6 px-4 py-3 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
    <?php echo sanitize($flash['message']); ?>
  </div>
<?php endif; ?>
