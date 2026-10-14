<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$order_id = (int) ($_GET['id'] ?? 0);

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id");
$stmt->bindParam(':id', $order_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch();

if (!$order) {
    redirect('/index.php');
}

$itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :id");
$itemsStmt->bindParam(':id', $order_id);
$itemsStmt->execute();
$items = $itemsStmt->fetchAll();

$page_title = "Order Confirmed";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-lg mx-auto bg-white rounded-xl shadow p-8 text-center">
  <p class="text-5xl mb-4">✅</p>
  <h1 class="text-2xl font-bold mb-2">Order Placed Successfully!</h1>
  <p class="text-gray-500 mb-6">Order #<?php echo $order['id']; ?> — Thank you for shopping with FreshMart.</p>

  <div class="text-left border-t pt-4">
    <?php foreach ($items as $item): ?>
      <div class="flex justify-between text-sm mb-2">
        <span><?php echo sanitize($item['product_name']); ?> x<?php echo $item['quantity']; ?></span>
        <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
      </div>
    <?php endforeach; ?>
    <div class="border-t mt-4 pt-4 flex justify-between font-bold">
      <span>Total</span>
      <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
    </div>
  </div>

  <a href="/index.php" class="inline-block mt-6 bg-brand text-white px-6 py-2 rounded-lg hover:bg-brand-dark transition">Continue Shopping</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
