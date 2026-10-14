<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('/cart.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');

        if (empty($full_name) || empty($address) || empty($phone)) {
            $errors[] = "Please fill in all delivery details.";
        }

        if (empty($errors)) {
            $database = new Database();
            $db = $database->getConnection();

            try {
                $db->beginTransaction();

                $total = cart_total();

                $stmt = $db->prepare("INSERT INTO orders (user_id, full_name, address, phone, total_amount, status) VALUES (:user_id, :full_name, :address, :phone, :total, 'pending')");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':total', $total);
                $stmt->execute();
                $order_id = $db->lastInsertId();

                foreach ($cart as $item) {
                    // Verify stock again server-side before finalizing
                    $stockCheck = $db->prepare("SELECT stock FROM products WHERE id = :id FOR UPDATE");
                    $stockCheck->bindParam(':id', $item['id']);
                    $stockCheck->execute();
                    $row = $stockCheck->fetch();

                    if (!$row || $row['stock'] < $item['quantity']) {
                        throw new Exception("Insufficient stock for " . $item['name']);
                    }

                    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (:order_id, :product_id, :product_name, :quantity, :price)");
                    $itemStmt->bindParam(':order_id', $order_id);
                    $itemStmt->bindParam(':product_id', $item['id']);
                    $itemStmt->bindParam(':product_name', $item['name']);
                    $itemStmt->bindParam(':quantity', $item['quantity']);
                    $itemStmt->bindParam(':price', $item['price']);
                    $itemStmt->execute();

                    $updateStock = $db->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :id");
                    $updateStock->bindParam(':quantity', $item['quantity']);
                    $updateStock->bindParam(':id', $item['id']);
                    $updateStock->execute();
                }

                $db->commit();
                unset($_SESSION['cart']);
                redirect('/order-success.php?id=' . $order_id);

            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = "Order failed: " . $e->getMessage();
            }
        }
    }
}

$page_title = "Checkout";
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="text-2xl font-bold mb-6">Checkout</h1>

<?php if (!empty($errors)): ?>
  <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 text-red-800 border border-red-300">
    <ul class="list-disc list-inside">
      <?php foreach ($errors as $error) echo "<li>" . sanitize($error) . "</li>"; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="grid md:grid-cols-3 gap-6">
  <div class="md:col-span-2 bg-white rounded-xl shadow p-6">
    <h2 class="font-semibold mb-4">Delivery Details</h2>
    <form method="POST" action="" id="checkout-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Full Name</label>
        <input type="text" name="full_name" required value="<?php echo sanitize($_SESSION['full_name'] ?? ''); ?>"
               class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Delivery Address</label>
        <textarea name="address" required rows="3"
                  class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand"></textarea>
      </div>

      <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Phone Number</label>
        <input type="tel" name="phone" required pattern="[0-9+\-\s]{7,15}"
               class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      </div>

      <button type="submit" class="w-full bg-brand text-white py-3 rounded-lg font-semibold hover:bg-brand-dark transition">
        Place Order (Cash on Delivery)
      </button>
    </form>
  </div>

  <div class="bg-white rounded-xl shadow p-6 h-fit">
    <h2 class="font-semibold mb-4">Order Summary</h2>
    <?php foreach ($cart as $item): ?>
      <div class="flex justify-between text-sm mb-2">
        <span><?php echo sanitize($item['name']); ?> x<?php echo $item['quantity']; ?></span>
        <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
      </div>
    <?php endforeach; ?>
    <div class="border-t mt-4 pt-4 flex justify-between font-bold">
      <span>Total</span>
      <span>₱<?php echo number_format(cart_total(), 2); ?></span>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
