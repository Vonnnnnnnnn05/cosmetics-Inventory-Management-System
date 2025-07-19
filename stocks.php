<?php
// stocks.php - Stock Alert UI for all products, filter by category
require_once 'conn.php';

// Get all unique categories for filter dropdown
$categoryResult = $conn->query('SELECT DISTINCT category FROM products');
$categories = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Get selected category from GET
$selectedCategory = $_GET['category'] ?? '';

// Fetch all products, filter by category if selected
$params = [];
$sql = 'SELECT * FROM products';
if ($selectedCategory && $selectedCategory !== 'all') {
    $sql .= ' WHERE category = ?';
    $params[] = $selectedCategory;
}
$sql .= ' ORDER BY category, name';
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param('s', ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt->execute();
    $result = $stmt->get_result();
}
$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stocks - Beauty Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff0f5; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background: #d63384; color: #fff; padding-top: 30px; transition: margin-left 0.3s, width 0.3s; }
        .sidebar-collapsed { margin-left: -180px; width: 0; overflow: hidden; }
        .sidebar .nav-link { color: #fff; font-weight: 500; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background: #fff; color: #d63384; border-radius: 8px; }
        .dashboard-content { padding: 40px 30px; }
        .sidebar-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 2rem; text-align: center; }
        .sidebar-toggle-btn { position: absolute; top: 20px; left: 210px; z-index: 1001; background: #d63384; color: #fff; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; transition: left 0.3s; }
        .sidebar-collapsed ~ .sidebar-toggle-btn { left: 30px; }
        @media (max-width: 768px) { .sidebar { position: absolute; z-index: 1000; } .sidebar-toggle-btn { left: 30px; } }
        .table thead { background: #d63384; color: #fff; }
        .badge-low { background: #d63384; }
        .status-in { color: #198754; font-weight: 500; }
        .status-low { color: #ffc107; font-weight: 500; }
        .status-out { color: #dc3545; font-weight: 500; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row position-relative">
        <nav id="sidebar" class="col-md-2 d-none d-md-block sidebar">
            <div class="sidebar-title">Admin</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link " href="products.php">Products</a></li>
                
                <li class="nav-item"><a class="nav-link active "  href="stocks.php">Stocks Alert</a></li>
                <li class="nav-item"><a class="nav-link" href="sales.php">Sales</a></li>
                 <li class="nav-item"><a class="nav-link" href="print_report.php?type=sales">Print Sales Report</a></li>
                <li class="nav-item"><a class="nav-link" href="print_report.php?type=products">Print Products Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <button id="sidebarToggle" class="sidebar-toggle-btn" title="Toggle Sidebar">&#9776;</button>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 dashboard-content">
            <div class="d-flex justify-content-between align-items-center my-4">
                <h2>Stocks</h2>
                <form class="d-flex" method="get" action="stocks.php" style="max-width:350px;">
                    <select class="form-select me-2" name="category" onchange="this.form.submit()">
                        <option value="all"<?= $selectedCategory === '' || $selectedCategory === 'all' ? ' selected' : '' ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"<?= $selectedCategory === $cat ? ' selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="btn btn-outline-secondary" type="submit">Filter</button></noscript>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['id']) ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                          <td>₱<?= number_format($product['price'], 2) ?></td>
                            <td><span class="badge <?= $product['stock'] == 0 ? 'bg-danger' : ($product['stock'] <= 10 ? 'badge-low' : 'bg-success') ?>"><?= htmlspecialchars($product['stock']) ?></span></td>
                            <td>
                                <?php if ($product['stock'] == 0): ?>
                                    <span class="status-out">Out of Stock</span>
                                <?php elseif ($product['stock'] <= 10): ?>
                                    <span class="status-low">Low Stock</span>
                                <?php else: ?>
                                    <span class="status-in">In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                        <tr><td colspan="6" class="text-center">No products found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    let collapsed = false;
    toggleBtn.addEventListener('click', function() {
        collapsed = !collapsed;
        sidebar.classList.toggle('sidebar-collapsed', collapsed);
        toggleBtn.style.left = collapsed ? '30px' : '210px';
    });
</script>
</body>
</html>
