<?php
/**
 * ONE-TIME SETUP SCRIPT
 * ----------------------
 * Run this file once in your browser after importing database/schema.sql
 * to create the default admin account. It uses PHP's own password_hash()
 * function so the password is guaranteed to work on your server.
 *
 * Default admin login created:
 *   Email:    admin@freshmart.com
 *   Password: Admin@123
 *
 * IMPORTANT: Delete this file from your server immediately after running
 * it once, so no one else can use it to create additional admin accounts.
 */
require_once __DIR__ . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

$email = 'admin@freshmart.com';
$password = 'Admin@123';
$full_name = 'Store Admin';

$check = $db->prepare("SELECT id FROM users WHERE email = :email");
$check->bindParam(':email', $email);
$check->execute();

if ($check->rowCount() > 0) {
    echo "An account with this email already exists. Setup already completed. Please delete this file.";
    exit;
}

$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = $db->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, 'admin')");
$stmt->bindParam(':full_name', $full_name);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $hashed);
$stmt->execute();

echo "Admin account created successfully!<br>";
echo "Email: $email<br>";
echo "Password: $password<br><br>";
echo "<strong>IMPORTANT: Delete this file (setup_admin.php) from your server now.</strong>";
