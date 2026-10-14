<?php
$page_title = "Dashboard";
require_once __DIR__ . '/admin_header.php';

$database = new Database();
$db = $database->getConnection();

$total_products = $db->query("SELECT COUNT(*) as c FROM products")->fetch()['c'];
$total_orders = $db->query("SELECT COUNT(*) as c FROM orders")->fetch()['c'];
$total_customers = $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'customer'")->fetch()['c'];
$total_revenue = $db->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE status != 'cancelled'")->fetch()['s'];
$low_stock = $db->query("SELECT * FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 5")->fetchAll();
$recent_orders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <div class="bg-white rounded-xl shadow p-5">
    <p class="text-sm text-gray-500">Total Products</p>
    <p class="text-3xl font-bold"><?php echo $total_products; ?></p>
  </div>
  <div class="bg-white rounded-xl shadow p-5">
    <p class="text-sm text-gray-500">Total Orders</p>
    <p class="text-3xl font-bold"><?php echo $total_orders; ?></p>
  </div>
  <div class="bg-white rounded-xl shadow p-5">
    <p class="text-sm text-gray-500">Customers</p>
    <p class="text-3xl font-bold"><?php echo $total_customers; ?></p>
  </div>
  <div class="bg-white rounded-xl shadow p-5">
    <p class="text-sm text-gray-500">Total Revenue</p>
    <p class="text-3xl font-bold text-brand">₱<?php echo number_format($total_revenue, 2); ?></p>
  </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
  <div class="bg-white rounded-xl shadow p-5">
    <h2 class="font-semibold mb-4">Recent Orders</h2>
    <?php if (empty($recent_orders)): ?>
      <p class="text-gray-500 text-sm">No orders yet.</p>
    <?php else: ?>
      <table class="w-full text-sm text-left">
        <?php foreach ($recent_orders as $order): ?>
          <tr class="border-t">
            <td class="py-2">#<?php echo $order['id']; ?></td>
            <td class="py-2"><?php echo sanitize($order['full_name']); ?></td>
            <td class="py-2">₱<?php echo number_format($order['total_amount'], 2); ?></td>
            <td class="py-2 capitalize"><?php echo $order['status']; ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-xl shadow p-5">
    <h2 class="font-semibold mb-4">Low Stock Alert (≤ 10)</h2>
    <?php if (empty($low_stock)): ?>
      <p class="text-gray-500 text-sm">All products are well stocked.</p>
    <?php else: ?>
      <table class="w-full text-sm text-left">
        <?php foreach ($low_stock as $product): ?>
          <tr class="border-t">
            <td class="py-2"><?php echo sanitize($product['name']); ?></td>
            <td class="py-2 text-red-600 font-semibold"><?php echo $product['stock']; ?> left</td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
