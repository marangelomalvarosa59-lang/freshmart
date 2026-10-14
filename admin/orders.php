<?php
$page_title = "Manage Orders";
require_once __DIR__ . '/admin_header.php';

$database = new Database();
$db = $database->getConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash("Invalid request.", "error");
    } else {
        $order_id = (int) $_POST['order_id'];
        $status = $_POST['status'];
        $allowed = ['pending', 'processing', 'completed', 'cancelled'];
        if (in_array($status, $allowed, true)) {
            $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $order_id);
            $stmt->execute();
            set_flash("Order #$order_id updated to \"$status\".", "success");
        }
    }
    redirect('/admin/orders.php');
}

$orders = $db->query("
    SELECT o.*, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
")->fetchAll();

$status_colors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
];
?>

<div class="bg-white rounded-xl shadow overflow-x-auto">
  <table class="w-full text-left text-sm">
    <thead class="bg-gray-100 text-gray-600">
      <tr>
        <th class="p-4">Order #</th>
        <th class="p-4">Customer</th>
        <th class="p-4">Address / Phone</th>
        <th class="p-4">Total</th>
        <th class="p-4">Status</th>
        <th class="p-4">Update</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $order): ?>
        <tr class="border-t align-top">
          <td class="p-4">#<?php echo $order['id']; ?><br><span class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span></td>
          <td class="p-4"><?php echo sanitize($order['full_name']); ?><br><span class="text-xs text-gray-400"><?php echo sanitize($order['email']); ?></span></td>
          <td class="p-4 max-w-xs"><?php echo sanitize($order['address']); ?><br><span class="text-xs text-gray-400"><?php echo sanitize($order['phone']); ?></span></td>
          <td class="p-4">₱<?php echo number_format($order['total_amount'], 2); ?></td>
          <td class="p-4">
            <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$order['status']]; ?>">
              <?php echo ucfirst($order['status']); ?>
            </span>
          </td>
          <td class="p-4">
            <form method="POST" action="" class="flex gap-2">
              <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
              <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
              <select name="status" class="border rounded-lg px-2 py-1 text-sm">
                <?php foreach (array_keys($status_colors) as $status): ?>
                  <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="bg-brand text-white px-3 py-1 rounded-lg text-sm hover:bg-brand-dark transition">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
