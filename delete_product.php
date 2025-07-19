<?php
// delete_product.php - Handles deleting a product
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        header('Location: products.php?error=Invalid+product+ID');
        exit();
    }
    $stmt = $conn->prepare('DELETE FROM products WHERE id=?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        header('Location: products.php?success=Product+deleted+successfully');
        exit();
    } else {
        header('Location: products.php?error=Failed+to+delete+product');
        exit();
    }
} else {
    header('Location: products.php');
    exit();
}
