<?php
// edit_product.php - Handles editing a product
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');

    if ($id <= 0 || $name === '' || $category === '' || $price === '' || $stock === '') {
        header('Location: products.php?error=Please+fill+in+all+fields');
        exit();
    }

    $stmt = $conn->prepare('UPDATE products SET name=?, category=?, price=?, stock=? WHERE id=?');
    $stmt->bind_param('ssdii', $name, $category, $price, $stock, $id);
    if ($stmt->execute()) {
        header('Location: products.php?success=Product+updated+successfully');
        exit();
    } else {
        header('Location: products.php?error=Failed+to+update+product');
        exit();
    }
} else {
    header('Location: products.php');
    exit();
}
