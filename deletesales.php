<?php
// deletesales.php - Delete a sale record and restore stock
require_once 'conn.php';

function redirect($msg, $type = 'success') {
    header('Location: sales.php?' . $type . '=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_id = isset($_POST['sale_id']) ? intval($_POST['sale_id']) : 0;
    if (!$sale_id) {
        redirect('Invalid sale ID.', 'error');
    }

    // Get sale info
    $stmt = $conn->prepare('SELECT product_id, quantity FROM sales WHERE id = ?');
    $stmt->bind_param('i', $sale_id);
    $stmt->execute();
    $stmt->bind_result($product_id, $quantity);
    if (!$stmt->fetch()) {
        $stmt->close();
        redirect('Sale not found.', 'error');
    }
    $stmt->close();

    // Delete sale
    $stmt = $conn->prepare('DELETE FROM sales WHERE id = ?');
    $stmt->bind_param('i', $sale_id);
    if (!$stmt->execute()) {
        $stmt->close();
        redirect('Failed to delete sale.', 'error');
    }
    $stmt->close();

    // Restore product stock
    $stmt = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
    $stmt->bind_param('ii', $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    redirect('Sale deleted successfully!');
} else {
    redirect('Invalid request.', 'error');
}
