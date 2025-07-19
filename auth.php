<?php
// auth.php - Handles admin login authentication

session_start();
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header('Location: index.php?error=Please+enter+both+username+and+password');
        exit();
    }

    // Prepare and execute query
    $stmt = $conn->prepare('SELECT * FROM admin WHERE username = ? AND password = ?');
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        // Redirect to dashboard or home page
        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: index.php?error=Invalid+username+or+password');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
