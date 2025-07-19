<?php
require_once 'conn.php';
// Calculate total sales (sum of quantity * price)
$totalSales = 0;
if ($conn->query("SHOW TABLES LIKE 'sales'")->num_rows > 0) {
    $result = $conn->query('SELECT SUM(s.quantity * p.price) AS total FROM sales s JOIN products p ON s.product_id = p.id');
    if ($result && $row = $result->fetch_assoc()) {
        $totalSales = $row['total'] ?? 0;
    }
}
// Fetch product quantities by category for pie chart
$categoryData = [];
$result = $conn->query('SELECT category, SUM(stock) as total_quantity FROM products GROUP BY category');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categoryData[] = $row;
    }
}
// Fetch sales per month for bar graph
$salesPerMonth = [];
if ($conn->query("SHOW TABLES LIKE 'sales'")->num_rows > 0) {
    $result = $conn->query("SELECT DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(quantity * p.price) as total FROM sales s JOIN products p ON s.product_id = p.id GROUP BY month ORDER BY month");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $salesPerMonth[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Beauty Products Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #fff0f5;
            font-family: 'Segoe UI', sans-serif;
        }
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
            .sidebar {
                position: absolute;
                z-index: 1000;
            }
            .sidebar-toggle-btn {
                left: 30px;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row position-relative">
         <nav id="sidebar" class="col-md-2 d-none d-md-block sidebar">
            <div class="sidebar-title">Admin</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="stocks.php">Stocks Alert</a></li>
                <li class="nav-item"><a class="nav-link" href="sales.php">Sales</a></li>
                <li class="nav-item"><a class="nav-link" href="print_report.php?type=sales">Print Sales Report</a></li>
                <li class="nav-item"><a class="nav-link" href="print_report.php?type=products">Print Products Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <button id="sidebarToggle" class="sidebar-toggle-btn" title="Toggle Sidebar">&#9776;</button>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 dashboard-content">
            <h1 class="mb-4">Welcome to the Admin Dashboard</h1>
            <p>Use the sidebar to navigate through the inventory management system.</p>
            <div class="alert alert-info" role="alert">
                Total Sales: $<?php echo number_format($totalSales, 2); ?>
            </div>
            <!-- Add dashboard widgets or stats here -->
            <div class="row mb-4">
                <div class="col-md-4 d-flex flex-column justify-content-between">
                    <div class="card shadow-sm border-0 mb-3 flex-fill">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-2" style="color:#d63384;">Total Sales</h5>
                            <p class="display-6 fw-bold">₱<?= number_format($totalSales, 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex flex-column justify-content-between">
                    <div class="card shadow-sm border-0 mb-3 flex-fill">
                        <div class="card-body">
                            <h5 class="card-title mb-2" style="color:#d63384;">Products by Category</h5>
                            <canvas id="categoryPieChart" height="180" width="180" style="max-width:100%;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex flex-column justify-content-between">
                    <div class="card shadow-sm border-0 mb-3 flex-fill">
                        <div class="card-body">
                            <h5 class="card-title mb-2" style="color:#d63384;">Sales Per Month</h5>
                            <canvas id="salesBarChart" height="180" width="220" style="max-width:100%;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    let collapsed = false;
    toggleBtn.addEventListener('click', function() {
        collapsed = !collapsed;
        sidebar.classList.toggle('sidebar-collapsed', collapsed);
        if (collapsed) {
            toggleBtn.style.left = '30px';
        } else {
            toggleBtn.style.left = '210px';
        }
    });

    // Pie chart for products by category
    const categoryData = <?php echo json_encode($categoryData); ?>;
    const ctxPie = document.getElementById('categoryPieChart').getContext('2d');
    const pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: categoryData.map(item => item.category),
            datasets: [{
                data: categoryData.map(item => item.total_quantity),
                backgroundColor: [
                    '#d63384', '#f06292', '#f8bbd0', '#ba68c8', '#ffd6e0', '#ffb6b9', '#c2185b', '#ad1457'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: false }
            }
        }
    });

    // Bar graph for sales per month
    const salesPerMonth = <?php echo json_encode($salesPerMonth); ?>;
    const salesBarCtx = document.getElementById('salesBarChart').getContext('2d');
    const salesBarChart = new Chart(salesBarCtx, {
        type: 'bar',
        data: {
            labels: salesPerMonth.map(item => item.month),
            datasets: [{
                label: 'Total Sales (₱)',
                data: salesPerMonth.map(item => item.total),
                backgroundColor: '#d63384',
                borderRadius: 6,
                maxBarThickness: 30
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return '₱' + value.toLocaleString(); }
                    }
                }
            }
        }
    });
</script>
</body>
</html>
