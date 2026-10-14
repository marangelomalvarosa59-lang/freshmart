<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? sanitize($page_title) . ' - Admin' : 'Admin Panel'; ?> | FreshMart</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/css/style.css">
<script>
  tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT: '#16a34a', dark: '#15803d', light: '#dcfce7' } } } } }
</script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

<div class="flex min-h-screen">
  <aside class="w-56 bg-gray-900 text-white flex-shrink-0 hidden md:block">
    <div class="p-5 text-xl font-bold border-b border-gray-700">🛒 FreshMart <span class="text-xs font-normal text-gray-400">Admin</span></div>
    <nav class="p-4 flex flex-col gap-1 text-sm">
      <a href="/admin/index.php" class="px-3 py-2 rounded-lg hover:bg-gray-800 transition">Dashboard</a>
      <a href="/admin/products.php" class="px-3 py-2 rounded-lg hover:bg-gray-800 transition">Products</a>
      <a href="/admin/categories.php" class="px-3 py-2 rounded-lg hover:bg-gray-800 transition">Categories</a>
      <a href="/admin/orders.php" class="px-3 py-2 rounded-lg hover:bg-gray-800 transition">Orders</a>
      <a href="/index.php" class="px-3 py-2 rounded-lg hover:bg-gray-800 transition mt-4">← Back to Store</a>
      <a href="/auth/logout.php" class="px-3 py-2 rounded-lg hover:bg-gray-800 transition">Logout</a>
    </nav>
  </aside>

  <div class="flex-grow">
    <header class="bg-white shadow flex items-center justify-between px-6 py-4">
      <h1 class="text-xl font-bold"><?php echo isset($page_title) ? sanitize($page_title) : 'Admin Panel'; ?></h1>
      <span class="text-sm text-gray-500">Logged in as <?php echo sanitize($_SESSION['full_name']); ?></span>
    </header>

    <main class="p-6">
      <?php $flash = get_flash(); if ($flash): ?>
        <div class="mb-6 px-4 py-3 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
          <?php echo sanitize($flash['message']); ?>
        </div>
      <?php endif; ?>
