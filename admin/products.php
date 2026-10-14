<?php
$page_title = "Manage Products";
require_once __DIR__ . '/admin_header.php';

$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    if (!verify_csrf($_GET['csrf_token'] ?? '')) {
        set_flash("Invalid request.", "error");
    } else {
        $id = (int) $_GET['delete'];
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        set_flash("Product deleted successfully.", "success");
    }
    redirect('/admin/products.php');
}

$products = $db->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
  <p class="text-gray-500">Total: <?php echo count($products); ?> products</p>
  <a href="/admin/product_form.php" class="bg-brand text-white px-4 py-2 rounded-lg hover:bg-brand-dark transition">+ Add Product</a>
</div>

<div class="bg-white rounded-xl shadow overflow-x-auto">
  <table class="w-full text-left text-sm">
    <thead class="bg-gray-100 text-gray-600">
      <tr>
        <th class="p-4">Name</th>
        <th class="p-4">Category</th>
        <th class="p-4">Price</th>
        <th class="p-4">Stock</th>
        <th class="p-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
        <tr class="border-t">
          <td class="p-4 font-medium"><?php echo sanitize($product['name']); ?></td>
          <td class="p-4"><?php echo sanitize($product['category_name'] ?? 'Uncategorized'); ?></td>
          <td class="p-4">₱<?php echo number_format($product['price'], 2); ?></td>
          <td class="p-4"><?php echo $product['stock']; ?></td>
          <td class="p-4 flex gap-3">
            <a href="/admin/product_form.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
            <a href="/admin/products.php?delete=<?php echo $product['id']; ?>&csrf_token=<?php echo csrf_token(); ?>"
               onclick="return confirm('Delete this product? This cannot be undone.');"
               class="text-red-600 hover:underline">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
