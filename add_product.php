<?php
require_once 'conn.php';

// Check if all required fields are present
if (isset($_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    // Basic validation
    if ($name === '' || $category === '' || $price <= 0 || $stock < 0) {
        header("Location: products.php?error=Please fill out all fields correctly&name=$name&category=$category&price=$price&stock=$stock");
        exit();
    }

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $category, $price, $stock);

    if ($stmt->execute()) {
        header("Location: products.php?success=Product added successfully");
    } else {
        header("Location: products.php?error=Failed to add product");
    }

    $stmt->close();
} else {
    header("Location: products.php?error=Please fill out all fields");
}
