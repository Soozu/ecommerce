<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select database
if (!$conn->select_db(DB_NAME)) {
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === TRUE) {
        $conn->select_db(DB_NAME);
    } else {
        die("Error creating database: " . $conn->error);
    }
}

// Check if status column exists in products table
$check_status = "SHOW COLUMNS FROM products LIKE 'status'";
$result = $conn->query($check_status);

if ($result && $result->num_rows === 0) {
    // Add status column if it doesn't exist
    $add_status = "ALTER TABLE products 
                   ADD COLUMN status ENUM('active', 'inactive', 'draft') DEFAULT 'active'";
    $conn->query($add_status);
}

// Update any NULL statuses to 'active'
$update_status = "UPDATE products SET status = 'active' WHERE status IS NULL";
$conn->query($update_status);

// Update any NULL category_ids based on category name
$update_categories = "UPDATE products p 
                     LEFT JOIN categories c ON p.category = c.name 
                     SET p.category_id = c.id 
                     WHERE p.category_id IS NULL";
$conn->query($update_categories);
?> 