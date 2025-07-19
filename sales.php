<?php
// sales.php - Sales UI for Beauty Admin
require_once 'conn.php';

// Fetch all products for dropdown
$products = [];
$result = $conn->query('SELECT id, name, category, price, stock FROM products ORDER BY name');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch sales (assuming a 'sales' table exists)
$sales = [];
if ($conn->query("SHOW TABLES LIKE 'sales'")->num_rows > 0) {
    $result = $conn->query('SELECT s.id, s.product_id, s.quantity, s.sale_date, p.name, p.category, p.price FROM sales s JOIN products p ON s.product_id = p.id ORDER BY s.sale_date DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales - Beauty Admin</title>
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
        .btn-pink { background: #d63384; color: #fff; }
        .btn-pink:hover { background: #c2185b; color: #fff; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row position-relative">
        <nav id="sidebar" class="col-md-2 d-none d-md-block sidebar">
            <div class="sidebar-title">Admin</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="stocks.php">Stocks Alert</a></li>
                <li class="nav-item"><a class="nav-link active" href="sales.php">Sales</a></li>
                 <li class="nav-item"><a class="nav-link" href="print_report.php?type=sales">Print Sales Report</a></li>
                <li class="nav-item"><a class="nav-link" href="print_report.php?type=products">Print Products Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <button id="sidebarToggle" class="sidebar-toggle-btn" title="Toggle Sidebar">&#9776;</button>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 dashboard-content">
            <div class="d-flex justify-content-between align-items-center my-4">
                <h2>Sales</h2>
                <button class="btn btn-pink" data-bs-toggle="modal" data-bs-target="#addSaleModal">Add Sale</button>
            </div>
            <!-- Add Sale Modal -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="modal fade" id="addSaleModal" tabindex="-1" aria-labelledby="addSaleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="addsales.php" method="POST" autocomplete="off">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSaleModalLabel">Add Sale</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="saleProduct" class="form-label">Product</label>
                                    <select class="form-select" id="saleProduct" name="product_id" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['category']) ?>) - Stock: <?= $p['stock'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="saleQuantity" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="saleQuantity" name="quantity" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="saleDate" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="saleDate" name="sale_date" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-pink">Add Sale</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Add Sale Modal -->
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th><th>Product</th><th>Category</th><th>Quantity</th><th>Price</th><th>Total</th><th>Date</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['id']) ?></td>
                            <td><?= htmlspecialchars($sale['name']) ?></td>
                            <td><?= htmlspecialchars($sale['category']) ?></td>
                            <td><?= htmlspecialchars($sale['quantity']) ?></td>
                            <td>₱<?= number_format($sale['price'], 2) ?></td>
                            <td><?= htmlspecialchars(number_format($sale['price'] * $sale['quantity'], 2)) ?></td>
                            <td><?= htmlspecialchars(date('F j, Y', strtotime($sale['sale_date']))) ?></td>
                            <td>
                                <!-- Edit Button -->
                                <button class="btn btn-sm" style="background:#f06292;color:#fff;" data-bs-toggle="modal" data-bs-target="#editSaleModal<?= $sale['id'] ?>">Edit</button>
                                <!-- Delete Button -->
                                <button class="btn btn-sm" style="background:#f8bbd0;color:#d63384;" data-bs-toggle="modal" data-bs-target="#deleteSaleModal<?= $sale['id'] ?>">Delete</button>
                            </td>
                        </tr>
                        <!-- Edit Sale Modal -->
                        <div class="modal fade" id="editSaleModal<?= $sale['id'] ?>" tabindex="-1" aria-labelledby="editSaleModalLabel<?= $sale['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="editsales.php" method="POST" autocomplete="off">
                                        <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editSaleModalLabel<?= $sale['id'] ?>">Edit Sale</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Product</label>
                                                <select class="form-select" name="product_id" required>
                                                    <option value="">Select Product</option>
                                                    <?php foreach ($products as $p): ?>
                                                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $sale['product_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['category']) ?>) - Stock: <?= $p['stock'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" class="form-control" name="quantity" min="1" value="<?= $sale['quantity'] ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" class="form-control" name="sale_date" value="<?= htmlspecialchars($sale['sale_date']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn" style="background:#f06292;color:#fff;">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Delete Sale Modal -->
                        <div class="modal fade" id="deleteSaleModal<?= $sale['id'] ?>" tabindex="-1" aria-labelledby="deleteSaleModalLabel<?= $sale['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="deletesales.php" method="POST">
                                        <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteSaleModalLabel<?= $sale['id'] ?>">Delete Sale</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete this sale record?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn" style="background:#f8bbd0;color:#d63384;">Delete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($sales)): ?>
                        <tr><td colspan="8" class="text-center">No sales recorded.</td></tr>
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
