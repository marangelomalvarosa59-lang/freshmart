<?php
require_once __DIR__ . '/includes/functions.php';

$cart = $_SESSION['cart'] ?? [];
$page_title = "My Cart";
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="text-2xl font-bold mb-6">Your Shopping Cart</h1>

<?php if (empty($cart)): ?>
  <div id="empty-cart-state" class="text-center py-16 bg-white rounded-xl shadow">
    <p class="text-5xl mb-4">🛒</p>
    <p class="text-gray-500 mb-4">Your cart is empty.</p>
    <a href="/index.php" class="bg-brand text-white px-6 py-2 rounded-lg hover:bg-brand-dark transition">Start Shopping</a>
  </div>
<?php else: ?>
  <div id="empty-cart-state" class="hidden text-center py-16 bg-white rounded-xl shadow">
    <p class="text-5xl mb-4">🛒</p>
    <p class="text-gray-500 mb-4">Your cart is empty.</p>
    <a href="/index.php" class="bg-brand text-white px-6 py-2 rounded-lg hover:bg-brand-dark transition">Start Shopping</a>
  </div>

  <div id="cart-table-wrapper" class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-100 text-sm text-gray-600">
        <tr>
          <th class="p-4">Product</th>
          <th class="p-4">Price</th>
          <th class="p-4">Quantity</th>
          <th class="p-4">Subtotal</th>
          <th class="p-4"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart as $item): ?>
          <tr class="cart-row border-t">
            <td class="p-4 font-medium"><?php echo sanitize($item['name']); ?></td>
            <td class="p-4">₱<?php echo number_format($item['price'], 2); ?></td>
            <td class="p-4">
              <input type="number" min="1" value="<?php echo $item['quantity']; ?>"
                     class="cart-qty-input w-20 border rounded-lg px-2 py-1"
                     data-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
            </td>
            <td class="p-4" id="subtotal-<?php echo $item['id']; ?>">
              ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
            </td>
            <td class="p-4">
              <button class="cart-remove-btn text-red-600 hover:underline" data-id="<?php echo $item['id']; ?>">Remove</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="flex justify-between items-center p-4 bg-gray-50 border-t">
      <span class="text-lg font-semibold">Total: <span id="cart-total">₱<?php echo number_format(cart_total(), 2); ?></span></span>
      <a href="/checkout.php" class="bg-brand text-white px-6 py-2 rounded-lg hover:bg-brand-dark transition">Proceed to Checkout</a>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
