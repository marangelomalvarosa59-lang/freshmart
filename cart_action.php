<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$database = new Database();
$db = $database->getConnection();

switch ($action) {
    case 'add':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        $stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id = :id");
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        $current_qty = $_SESSION['cart'][$product_id]['quantity'] ?? 0;
        if ($current_qty + $quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
            exit;
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => $product['name'] . ' added to cart!',
            'cart_count' => cart_count(),
            'cart_total' => number_format(cart_total(), 2)
        ]);
        break;

    case 'update':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);

        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            }
        }

        echo json_encode([
            'success' => true,
            'cart_count' => cart_count(),
            'cart_total' => number_format(cart_total(), 2)
        ]);
        break;

    case 'remove':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        unset($_SESSION['cart'][$product_id]);

        echo json_encode([
            'success' => true,
            'cart_count' => cart_count(),
            'cart_total' => number_format(cart_total(), 2)
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
