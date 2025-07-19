<?php
require_once 'conn.php';
$type = $_GET['type'] ?? '';
header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Print Report</title>';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<style>body{background:#fff0f5;font-family:Segoe UI,sans-serif;}@media print{.noprint{display:none;}} .table-pink th {background:#d63384!important;color:#fff!important;} .btn-primary{background:#d63384;border:none;} .btn-primary:hover{background:#c2185b;} .btn-secondary{background:#f8bbd0;color:#d63384;border:none;} .btn-secondary:hover{background:#f06292;color:#fff;} table.table-bordered td, table.table-bordered th {border-color:#f8bbd0!important;} h2{color:#d63384;}</style>';
echo '</head><body class="container py-4">';
echo '<div class="noprint mb-3"><button class="btn btn-primary" onclick="window.print()">Print</button> <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a></div>';

if ($type === 'sales') {
    echo '<h2 class="mb-4">Sales Report</h2>';
    $result = $conn->query('SELECT s.id, s.sale_date, p.name, p.category, s.quantity, p.price, (s.quantity * p.price) as total FROM sales s JOIN products p ON s.product_id = p.id ORDER BY s.sale_date DESC');
    if ($result && $result->num_rows > 0) {
        echo '<table class="table table-bordered"><thead class="table-pink"><tr><th>ID</th><th>Date</th><th>Product</th><th>Category</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead><tbody>';
        $grandTotal = 0;
        while ($row = $result->fetch_assoc()) {
            $grandTotal += $row['total'];
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['sale_date']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
            echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
            echo '<td>₱' . number_format($row['price'], 2) . '</td>';
            echo '<td>₱' . number_format($row['total'], 2) . '</td>';
            echo '</tr>';
        }
        echo '<tr class="fw-bold"><td colspan="6" class="text-end">Grand Total</td><td>₱' . number_format($grandTotal, 2) . '</td></tr>';
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-info">No sales records found.</div>';
    }
} elseif ($type === 'products') {
    echo '<h2 class="mb-4">Products Report</h2>';
    $result = $conn->query('SELECT id, name, category, price, stock FROM products ORDER BY name');
    if ($result && $result->num_rows > 0) {
        echo '<table class="table table-bordered"><thead class="table-pink"><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
            echo '<td>₱' . number_format($row['price'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($row['stock']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-info">No products found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid report type.</div>';
}
echo '</body></html>';
