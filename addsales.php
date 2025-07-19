<?php
// addsales.php - Handles adding a sale from the Add Sale modal
require_once 'conn.php';

// Helper: redirect with message
function redirect($msg, $type = 'success') {
    header('Location: sales.php?' . $type . '=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $sale_date = isset($_POST['sale_date']) ? $_POST['sale_date'] : '';

    // Validate
    if (!$product_id || !$quantity || !$sale_date) {
        redirect('All fields are required.', 'error');
    }
    if ($quantity < 1) {
        redirect('Quantity must be at least 1.', 'error');
    }

    // Check product and stock
    $stmt = $conn->prepare('SELECT name, stock FROM products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $stmt->bind_result($product_name, $stock);
    if (!$stmt->fetch()) {
        $stmt->close();
        redirect('Product not found.', 'error');
    }
    $stmt->close();
    if ($stock < $quantity) {
        redirect('Not enough stock for ' . htmlspecialchars($product_name) . '.', 'error');
    }

    // Insert sale
    $stmt = $conn->prepare('INSERT INTO sales (product_id, quantity, sale_date) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $product_id, $quantity, $sale_date);
    if (!$stmt->execute()) {
        $stmt->close();
        redirect('Failed to add sale.', 'error');
    }
    $stmt->close();

    // Decrement stock
    $stmt = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
    $stmt->bind_param('ii', $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    redirect('Sale added successfully!');
} else {
    redirect('Invalid request.', 'error');
}
