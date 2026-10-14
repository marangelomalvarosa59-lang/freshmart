<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->fetchAll();

$page_title = "My Orders";
require_once __DIR__ . '/includes/header.php';

$status_colors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
];
?>

<h1 class="text-2xl font-bold mb-6">My Orders</h1>

<?php if (empty($orders)): ?>
  <p class="text-center text-gray-500 py-12">You haven't placed any orders yet.</p>
<?php else: ?>
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-100 text-sm text-gray-600">
        <tr>
          <th class="p-4">Order #</th>
          <th class="p-4">Date</th>
          <th class="p-4">Total</th>
          <th class="p-4">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr class="border-t">
            <td class="p-4">#<?php echo $order['id']; ?></td>
            <td class="p-4"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
            <td class="p-4">₱<?php echo number_format($order['total_amount'], 2); ?></td>
            <td class="p-4">
              <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$order['status']]; ?>">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
