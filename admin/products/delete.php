<?php
require_once '../../config/database.php';
require_once '../includes/auth.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    // Check if product exists and can be deleted
    $check_sql = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['count'] > 0) {
        // Product has orders, just deactivate it
        $sql = "UPDATE products SET status = 'inactive' WHERE id = ?";
    } else {
        // No orders, safe to delete
        $sql = "DELETE FROM products WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        header('Location: ../products.php?success=Product deleted successfully');
        exit;
    }
}

header('Location: ../products.php?error=Error deleting product');
exit; 