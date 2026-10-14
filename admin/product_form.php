<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$database = new Database();
$db = $database->getConnection();

$is_edit = isset($_GET['id']);
$product = ['id' => '', 'name' => '', 'description' => '', 'price' => '', 'stock' => '', 'category_id' => ''];
$errors = [];

if ($is_edit) {
    $id = (int) $_GET['id'];
    $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $found = $stmt->fetch();
    if (!$found) redirect('/admin/products.php');
    $product = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $category_id = (int) ($_POST['category_id'] ?? 0);

        // Server-side validation
        if (empty($name)) $errors[] = "Product name is required.";
        if ($price <= 0) $errors[] = "Price must be greater than zero.";
        if ($stock < 0) $errors[] = "Stock cannot be negative.";
        if ($category_id <= 0) $errors[] = "Please select a category.";

        if (empty($errors)) {
            if ($is_edit) {
                $stmt = $db->prepare("UPDATE products SET name=:name, description=:description, price=:price, stock=:stock, category_id=:category_id WHERE id=:id");
                $stmt->bindParam(':id', $product['id']);
            } else {
                $stmt = $db->prepare("INSERT INTO products (name, description, price, stock, category_id) VALUES (:name, :description, :price, :stock, :category_id)");
            }
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->execute();

            set_flash("Product " . ($is_edit ? "updated" : "added") . " successfully.", "success");
            redirect('/admin/products.php');
        } else {
            // Repopulate for redisplay
            $product = ['id' => $product['id'], 'name' => $name, 'description' => $description, 'price' => $price, 'stock' => $stock, 'category_id' => $category_id];
        }
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$page_title = $is_edit ? "Edit Product" : "Add Product";
require_once __DIR__ . '/admin_header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 text-red-800 border border-red-300">
    <ul class="list-disc list-inside">
      <?php foreach ($errors as $error) echo "<li>" . sanitize($error) . "</li>"; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
  <form method="POST" action="" id="product-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

    <div class="mb-4">
      <label class="block text-sm font-medium mb-1">Product Name</label>
      <input type="text" name="name" required value="<?php echo sanitize($product['name']); ?>"
             class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium mb-1">Description</label>
      <textarea name="description" rows="3"
                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand"><?php echo sanitize($product['description']); ?></textarea>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium mb-1">Price (₱)</label>
        <input type="number" step="0.01" min="0.01" name="price" required value="<?php echo $product['price']; ?>"
               class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Stock Quantity</label>
        <input type="number" min="0" name="stock" required value="<?php echo $product['stock']; ?>"
               class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      </div>
    </div>

    <div class="mb-6">
      <label class="block text-sm font-medium mb-1">Category</label>
      <select name="category_id" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
        <option value="">Select category</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo $cat['id']; ?>" <?php echo (int)$product['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>>
            <?php echo sanitize($cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="bg-brand text-white px-6 py-2 rounded-lg hover:bg-brand-dark transition">
        <?php echo $is_edit ? 'Update Product' : 'Add Product'; ?>
      </button>
      <a href="/admin/products.php" class="bg-gray-100 px-6 py-2 rounded-lg hover:bg-gray-200 transition">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
