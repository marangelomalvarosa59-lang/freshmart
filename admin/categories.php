<?php
$page_title = "Manage Categories";
require_once __DIR__ . '/admin_header.php';

$database = new Database();
$db = $database->getConnection();

$errors = [];

// Handle delete
if (isset($_GET['delete'])) {
    if (!verify_csrf($_GET['csrf_token'] ?? '')) {
        set_flash("Invalid request.", "error");
    } else {
        $id = (int) $_GET['delete'];
        $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        set_flash("Category deleted.", "success");
    }
    redirect('/admin/categories.php');
}

// Handle add / edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $edit_id = (int) ($_POST['edit_id'] ?? 0);

        if (empty($name)) {
            $errors[] = "Category name is required.";
        } else {
            if ($edit_id > 0) {
                $stmt = $db->prepare("UPDATE categories SET name = :name WHERE id = :id");
                $stmt->bindParam(':id', $edit_id);
            } else {
                $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:name)");
            }
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            set_flash("Category saved successfully.", "success");
            redirect('/admin/categories.php');
        }
    }
}

$categories = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
?>

<?php if (!empty($errors)): ?>
  <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 text-red-800 border border-red-300">
    <?php foreach ($errors as $error) echo "<p>" . sanitize($error) . "</p>"; ?>
  </div>
<?php endif; ?>

<div class="grid md:grid-cols-3 gap-6">
  <div class="bg-white rounded-xl shadow p-5 h-fit">
    <h2 class="font-semibold mb-4">Add New Category</h2>
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <input type="text" name="name" required placeholder="Category name"
             class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-brand">
      <button type="submit" class="w-full bg-brand text-white py-2 rounded-lg hover:bg-brand-dark transition">Add Category</button>
    </form>
  </div>

  <div class="md:col-span-2 bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-100 text-gray-600">
        <tr>
          <th class="p-4">Name</th>
          <th class="p-4">Products</th>
          <th class="p-4">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr class="border-t">
            <td class="p-4"><?php echo sanitize($cat['name']); ?></td>
            <td class="p-4"><?php echo $cat['product_count']; ?></td>
            <td class="p-4 flex gap-3">
              <button type="button" class="text-blue-600 hover:underline edit-cat-btn"
                      data-id="<?php echo $cat['id']; ?>" data-name="<?php echo sanitize($cat['name']); ?>">Edit</button>
              <a href="/admin/categories.php?delete=<?php echo $cat['id']; ?>&csrf_token=<?php echo csrf_token(); ?>"
                 onclick="return confirm('Delete this category?');" class="text-red-600 hover:underline">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Category Modal -->
<div id="edit-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-sm">
    <h3 class="font-semibold mb-4">Edit Category</h3>
    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <input type="hidden" name="edit_id" id="edit_id">
      <input type="text" name="name" id="edit_name" required
             class="w-full border rounded-lg px-4 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-brand">
      <div class="flex gap-2">
        <button type="submit" class="flex-grow bg-brand text-white py-2 rounded-lg hover:bg-brand-dark transition">Save</button>
        <button type="button" id="close-modal" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.edit-cat-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('edit_id').value = btn.dataset.id;
    document.getElementById('edit_name').value = btn.dataset.name;
    const modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  });
});
document.getElementById('close-modal').addEventListener('click', () => {
  const modal = document.getElementById('edit-modal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
});
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
