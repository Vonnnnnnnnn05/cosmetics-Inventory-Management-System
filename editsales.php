<?php
// editsales.php - Edit a sale record
require_once 'conn.php';

function redirect($msg, $type = 'success') {
    header('Location: sales.php?' . $type . '=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_id = isset($_POST['sale_id']) ? intval($_POST['sale_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $sale_date = isset($_POST['sale_date']) ? $_POST['sale_date'] : '';

    if (!$sale_id || !$product_id || !$quantity || !$sale_date) {
        redirect('All fields are required.', 'error');
    }
    if ($quantity < 1) {
        redirect('Quantity must be at least 1.', 'error');
    }

    // Get old sale info
    $stmt = $conn->prepare('SELECT product_id, quantity FROM sales WHERE id = ?');
    $stmt->bind_param('i', $sale_id);
    $stmt->execute();
    $stmt->bind_result($old_product_id, $old_quantity);
    if (!$stmt->fetch()) {
        $stmt->close();
        redirect('Sale not found.', 'error');
    }
    $stmt->close();

    // If product changed, restore old stock and check new stock
    if ($old_product_id != $product_id) {
        // Restore old product stock
        $stmt = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        $stmt->bind_param('ii', $old_quantity, $old_product_id);
        $stmt->execute();
        $stmt->close();
    }

    // Check new product stock
    $stmt = $conn->prepare('SELECT stock FROM products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $stmt->bind_result($stock);
    if (!$stmt->fetch()) {
        $stmt->close();
        redirect('Product not found.', 'error');
    }
    $stmt->close();
    $stock_needed = $quantity - (($old_product_id == $product_id) ? $old_quantity : 0);
    if ($stock < $stock_needed) {
        redirect('Not enough stock for this product.', 'error');
    }

    // Update sale
    $stmt = $conn->prepare('UPDATE sales SET product_id = ?, quantity = ?, sale_date = ? WHERE id = ?');
    $stmt->bind_param('iisi', $product_id, $quantity, $sale_date, $sale_id);
    if (!$stmt->execute()) {
        $stmt->close();
        redirect('Failed to update sale.', 'error');
    }
    $stmt->close();

    // Update product stock
    if ($old_product_id == $product_id) {
        $diff = $old_quantity - $quantity;
        $stmt = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        $stmt->bind_param('ii', $diff, $product_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Deduct new product stock
        $stmt = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
        $stmt->bind_param('ii', $quantity, $product_id);
        $stmt->execute();
        $stmt->close();
    }

    redirect('Sale updated successfully!');
} else {
    redirect('Invalid request.', 'error');
}
