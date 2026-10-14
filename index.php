<?php
require_once __DIR__ . '/includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Fetch categories for the filter bar
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Handle search + category filter using prepared statements
$search = sanitize($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND name LIKE :search";
    $params[':search'] = '%' . $search . '%';
}
if ($category_id > 0) {
    $sql .= " AND category_id = :category_id";
    $params[':category_id'] = $category_id;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = "Home";
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-brand-light rounded-2xl p-8 mb-8 text-center">
  <h1 class="text-3xl md:text-4xl font-bold text-brand-dark mb-2">Fresh Groceries, Delivered to Your Door</h1>
  <p class="text-gray-600">Quality produce, dairy, meat, and pantry essentials at great prices.</p>
</div>

<div class="flex flex-col md:flex-row gap-4 mb-8">
  <form method="GET" action="" class="flex-grow flex gap-2">
    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Search products..."
           class="flex-grow border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
    <?php if ($category_id > 0): ?>
      <input type="hidden" name="category" value="<?php echo $category_id; ?>">
    <?php endif; ?>
    <button type="submit" class="bg-brand text-white px-5 py-2 rounded-lg hover:bg-brand-dark transition">Search</button>
  </form>

  <select id="category-filter" class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand"
          onchange="window.location.href = this.value">
    <option value="/index.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" <?php echo $category_id === 0 ? 'selected' : ''; ?>>All Categories</option>
    <?php foreach ($categories as $cat): ?>
      <?php $url = '/index.php?category=' . $cat['id'] . ($search ? '&search=' . urlencode($search) : ''); ?>
      <option value="<?php echo $url; ?>" <?php echo $category_id === (int)$cat['id'] ? 'selected' : ''; ?>>
        <?php echo sanitize($cat['name']); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<?php if (empty($products)): ?>
  <p class="text-center text-gray-500 py-12">No products found. Try a different search or category.</p>
<?php else: ?>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
  <?php foreach ($products as $product): ?>
    <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">
      <div class="h-36 bg-brand-light flex items-center justify-center text-5xl">🛒</div>
      <div class="p-4 flex flex-col flex-grow">
        <h3 class="font-semibold mb-1"><?php echo sanitize($product['name']); ?></h3>
        <p class="text-sm text-gray-500 mb-2 flex-grow"><?php echo sanitize(substr($product['description'], 0, 60)); ?></p>
        <div class="flex items-center justify-between mb-3">
          <span class="text-brand font-bold">₱<?php echo number_format($product['price'], 2); ?></span>
          <span class="text-xs text-gray-400"><?php echo $product['stock']; ?> in stock</span>
        </div>
        <button
          class="add-to-cart-btn w-full bg-brand text-white py-2 rounded-lg hover:bg-brand-dark transition disabled:bg-gray-300"
          data-id="<?php echo $product['id']; ?>"
          data-name="<?php echo sanitize($product['name']); ?>"
          <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
          <?php echo $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
        </button>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="toast" class="fixed bottom-6 right-6 bg-gray-900 text-white px-5 py-3 rounded-lg shadow-lg hidden transition-opacity"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
