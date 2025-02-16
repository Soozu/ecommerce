<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch software orders with product details
$sql = "SELECT o.id as order_id, o.created_at, o.total_amount,
               oi.quantity, oi.price as item_price,
               p.name as product_name, p.description, p.category
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ? AND p.category = 'Software'
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'order_id' => $order_id,
            'date' => $row['created_at'],
            'total' => $row['total_amount'],
            'items' => []
        ];
    }
    $orders[$order_id]['items'][] = [
        'name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'price' => $row['item_price'],
        'description' => $row['description']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - TechGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/orders.css">
</head>
<body>
    <!-- Include your navigation here -->
    
    <div class="orders-bg">
        <div class="container">
            <div class="orders-container">
                <h1>Software Purchase History</h1>
                
                <?php if(empty($orders)): ?>
                    <div class="empty-orders">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No Software Purchases Yet</h3>
                        <p>You haven't purchased any software products yet.</p>
                        <a href="index.php" class="btn btn-primary">Browse Products</a>
                    </div>
                <?php else: ?>
                    <div class="orders-timeline">
                        <?php foreach($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h3>Order #<?php echo $order['order_id']; ?></h3>
                                        <div class="order-meta">
                                            <span class="date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('F j, Y', strtotime($order['date'])); ?>
                                            </span>
                                            <span class="time">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('g:i A', strtotime($order['date'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="order-total">
                                        <span class="label">Total:</span>
                                        <span class="amount">₱<?php echo number_format($order['total'], 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="order-items">
                                    <?php foreach($order['items'] as $item): ?>
                                        <div class="order-item">
                                            <div class="item-icon">
                                                <i class="fas fa-gamepad"></i>
                                            </div>
                                            <div class="item-details">
                                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                                <div class="item-meta">
                                                    <span class="quantity">
                                                        <i class="fas fa-times"></i>
                                                        <?php echo $item['quantity']; ?>
                                                    </span>
                                                    <span class="price">
                                                        ₱<?php echo number_format($item['price'], 2); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 