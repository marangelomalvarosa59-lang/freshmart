<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('/index.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = "Please enter both email and password.";
        } else {
            $database = new Database();
            $db = $database->getConnection();

            $stmt = $db->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    redirect('/admin/index.php');
                } else {
                    redirect('/index.php');
                }
            } else {
                $errors[] = "Invalid email or password.";
            }
        }
    }
}

$page_title = "Login";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow">
  <h1 class="text-2xl font-bold mb-6 text-center">Welcome Back</h1>

  <?php if (!empty($errors)): ?>
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 text-red-800 border border-red-300">
      <ul class="list-disc list-inside">
        <?php foreach ($errors as $error) echo "<li>" . sanitize($error) . "</li>"; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

    <div class="mb-4">
      <label class="block text-sm font-medium mb-1">Email Address</label>
      <input type="email" name="email" value="<?php echo $email; ?>" required
             class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
    </div>

    <div class="mb-6">
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password" name="password" required
             class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
    </div>

    <button type="submit" class="w-full bg-brand text-white py-2 rounded-lg font-semibold hover:bg-brand-dark transition">
      Log In
    </button>
  </form>

  <div class="mt-4 p-3 bg-gray-50 rounded-lg text-xs text-gray-500">
    Admin demo: admin@freshmart.com / Admin@123
  </div>

  <p class="text-center text-sm mt-6">Don't have an account? <a href="/auth/register.php" class="text-brand font-semibold">Sign up</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
