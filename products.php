<?php
// products.php - Admin product management
require_once 'conn.php';

// Fetch all products, with search
$products = [];
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $conn->prepare('SELECT * FROM products WHERE name LIKE ? OR category LIKE ?');
    $like = "%$search%";
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query('SELECT * FROM products');
}
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
    <title>Products - Beauty Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff0f5; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            min-height: 100vh;
            background: #d63384;
            color: #fff;
            padding-top: 30px;
            transition: margin-left 0.3s, width 0.3s;
        }
        .sidebar-collapsed {
            margin-left: -180px;
            width: 0;
            overflow: hidden;
        }
        .sidebar .nav-link {
            color: #fff;
            font-weight: 500;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: #fff;
            color: #d63384;
            border-radius: 8px;
        }
        .dashboard-content {
            padding: 40px 30px;
        }
        .sidebar-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-align: center;
        }
        .sidebar-toggle-btn {
            position: absolute;
            top: 20px;
            left: 210px;
            z-index: 1001;
            background: #d63384;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: left 0.3s;
        }
        .sidebar-collapsed ~ .sidebar-toggle-btn {
            left: 30px;
        }
        @media (max-width: 768px) {
            .sidebar { position: absolute; z-index: 1000; }
            .sidebar-toggle-btn { left: 30px; }
        }
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
                <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
                
                <li class="nav-item"><a class="nav-link" href="stocks.php">Stocks Alert</a></li>
                <li class="nav-item"><a class="nav-link" href="sales.php">Sales</a></li>
                 <li class="nav-item"><a class="nav-link" href="print_report.php?type=sales">Print Sales Report</a></li>
                <li class="nav-item"><a class="nav-link" href="print_report.php?type=products">Print Products Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <button id="sidebarToggle" class="sidebar-toggle-btn" title="Toggle Sidebar">&#9776;</button>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 dashboard-content">
            <!-- Flash messages -->
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

            <!-- Auto-open modal if error -->
            <?php if (isset($_GET['error']) && strpos($_GET['error'], 'fill') !== false): ?>
            <script>
                window.addEventListener('DOMContentLoaded', () => {
                    new bootstrap.Modal(document.getElementById('addProductModal')).show();
                });
            </script>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center my-4">
                <h2>Products</h2>
                <form class="d-flex" method="get" action="products.php" style="max-width:350px;">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </form>
                <button class="btn btn-pink ms-2" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
            </div>

            <!-- Add Product Modal -->
            <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="add_product.php" method="POST" autocomplete="off">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" required value="<?= $_GET['name'] ?? '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="productCategory" class="form-label">Category</label>
                                    <select class="form-select" id="productCategory" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="facial serum" <?= (isset($_GET['category']) && $_GET['category'] == 'facial serum') ? 'selected' : '' ?>>Facial Serum</option>
                                        <option value="concealer" <?= (isset($_GET['category']) && $_GET['category'] == 'concealer') ? 'selected' : '' ?>>Concealer</option>
                                        <option value="Face Mask" <?= (isset($_GET['category']) && $_GET['category'] == 'Face Mask') ? 'selected' : '' ?>>Face Mask</option>
                                        <option value="Nail Polish" <?= (isset($_GET['category']) && $_GET['category'] == 'Nail Polish') ? 'selected' : '' ?>>Nail Polish</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="productPrice" class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" name="price" required value="<?= $_GET['price'] ?? '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="productStock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" name="stock" required value="<?= $_GET['stock'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-pink">Add Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['id']) ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                           <td>₱<?= number_format($product['price'], 2) ?></td>
                            <td><?= htmlspecialchars($product['stock']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-pink" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $product['id'] ?>">Edit</button>
                                <button class="btn btn-sm btn-outline-danger" style="border-color:#d63384;color:#d63384;" data-bs-toggle="modal" data-bs-target="#deleteProductModal<?= $product['id'] ?>">Delete</button>
                            </td>
                        </tr>
                        <!-- Edit Product Modal -->
                        <div class="modal fade" id="editProductModal<?= $product['id'] ?>" tabindex="-1" aria-labelledby="editProductModalLabel<?= $product['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="edit_product.php" method="POST" autocomplete="off">
                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                        <div class="modal-header" style="background:#d63384;color:#fff;">
                                            <h5 class="modal-title" id="editProductModalLabel<?= $product['id'] ?>">Edit Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select class="form-select" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="facial serum" <?= $product['category'] == 'facial serum' ? 'selected' : '' ?>>Facial Serum</option>
                                                    <option value="concealer" <?= $product['category'] == 'concealer' ? 'selected' : '' ?>>Concealer</option>
                                                    <option value="Face Mask" <?= $product['category'] == 'Face Mask' ? 'selected' : '' ?>>Face Mask</option>
                                                    <option value="Nail Polish" <?= $product['category'] == 'Nail Polish' ? 'selected' : '' ?>>Nail Polish</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Price</label>
                                                <input type="number" step="0.01" class="form-control" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Stock</label>
                                                <input type="number" class="form-control" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-pink">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Delete Product Modal -->
                        <div class="modal fade" id="deleteProductModal<?= $product['id'] ?>" tabindex="-1" aria-labelledby="deleteProductModalLabel<?= $product['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="delete_product.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                        <div class="modal-header" style="background:#fff0f5;color:#d63384;">
                                            <h5 class="modal-title" id="deleteProductModalLabel<?= $product['id'] ?>">Delete Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete <strong><?= htmlspecialchars($product['name']) ?></strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-outline-danger" style="border-color:#d63384;color:#d63384;">Delete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
