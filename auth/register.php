<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('/index.php');
}

$errors = [];
$full_name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission. Please try again.";
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Server-side validation
        if (empty($full_name) || strlen($full_name) < 2) {
            $errors[] = "Please enter your full name.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($errors)) {
            $database = new Database();
            $db = $database->getConnection();

            // Check for duplicate email using a prepared statement
            $check = $db->prepare("SELECT id FROM users WHERE email = :email");
            $check->bindParam(':email', $email);
            $check->execute();

            if ($check->rowCount() > 0) {
                $errors[] = "An account with this email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, 'customer')");
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    set_flash("Account created successfully! Please log in.", "success");
                    redirect('/auth/login.php');
                } else {
                    $errors[] = "Something went wrong. Please try again.";
                }
            }
        }
    }
}

$page_title = "Create Account";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow">
  <h1 class="text-2xl font-bold mb-6 text-center">Create an Account</h1>

  <?php if (!empty($errors)): ?>
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 text-red-800 border border-red-300">
      <ul class="list-disc list-inside">
        <?php foreach ($errors as $error) echo "<li>" . sanitize($error) . "</li>"; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="" id="register-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

    <div class="mb-4">
      <label class="block text-sm font-medium mb-1">Full Name</label>
      <input type="text" name="full_name" id="full_name" value="<?php echo $full_name; ?>" required minlength="2"
             class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      <p class="text-red-600 text-sm mt-1 hidden" id="err-full_name"></p>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium mb-1">Email Address</label>
      <input type="email" name="email" id="email" value="<?php echo $email; ?>" required
             class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      <p class="text-red-600 text-sm mt-1 hidden" id="err-email"></p>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password" name="password" id="password" required minlength="6"
             class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      <p class="text-red-600 text-sm mt-1 hidden" id="err-password"></p>
    </div>

    <div class="mb-6">
      <label class="block text-sm font-medium mb-1">Confirm Password</label>
      <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
             class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
      <p class="text-red-600 text-sm mt-1 hidden" id="err-confirm_password"></p>
    </div>

    <button type="submit" class="w-full bg-brand text-white py-2 rounded-lg font-semibold hover:bg-brand-dark transition">
      Sign Up
    </button>
  </form>

  <p class="text-center text-sm mt-6">Already have an account? <a href="/auth/login.php" class="text-brand font-semibold">Log in</a></p>
</div>

<script>
document.getElementById('register-form').addEventListener('submit', function (e) {
  let valid = true;
  const fields = {
    full_name: v => v.trim().length >= 2 ? '' : 'Name must be at least 2 characters.',
    email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Enter a valid email address.',
    password: v => v.length >= 6 ? '' : 'Password must be at least 6 characters.',
    confirm_password: v => v === document.getElementById('password').value ? '' : 'Passwords do not match.'
  };

  for (const [id, validator] of Object.entries(fields)) {
    const input = document.getElementById(id);
    const errorEl = document.getElementById('err-' + id);
    const message = validator(input.value);
    if (message) {
      errorEl.textContent = message;
      errorEl.classList.remove('hidden');
      valid = false;
    } else {
      errorEl.classList.add('hidden');
    }
  }

  if (!valid) e.preventDefault();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
