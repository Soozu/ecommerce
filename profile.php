<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch active subscriptions count
$sub_sql = "SELECT COUNT(*) as count FROM subscriptions 
            WHERE user_id = ? AND status = 'active' 
            AND end_date > NOW()";
$stmt = $conn->prepare($sub_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_subs = $stmt->get_result()->fetch_assoc()['count'];

// Fetch recent orders
$orders_sql = "SELECT o.*, COUNT(oi.id) as items_count 
               FROM orders o 
               LEFT JOIN order_items oi ON o.id = oi.order_id 
               WHERE o.user_id = ? 
               GROUP BY o.id 
               ORDER BY o.created_at DESC 
               LIMIT 5";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total spent
$total_sql = "SELECT SUM(total_amount) as total FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($total_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_spent = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - TechGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <!-- Include your navigation here -->
    
    <div class="profile-bg">
        <div class="container">
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                        <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="member-since">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $active_subs; ?></h3>
                            <p>Active Subscriptions</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($recent_orders); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-info">
                            <h3>₱<?php echo number_format($total_spent, 2); ?></h3>
                            <p>Total Spent</p>
                        </div>
                    </div>
                </div>

                <div class="profile-sections">
                    <div class="section">
                        <div class="section-header">
                            <h2>Recent Orders</h2>
                            <a href="orders.php" class="btn btn-outline">View All</a>
                        </div>
                        <?php if(empty($recent_orders)): ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <p>No orders yet</p>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach($recent_orders as $order): ?>
                                    <div class="order-item">
                                        <div class="order-info">
                                            <h4>Order #<?php echo $order['id']; ?></h4>
                                            <p><?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                                        </div>
                                        <div class="order-meta">
                                            <span class="items"><?php echo $order['items_count']; ?> items</span>
                                            <span class="total">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="section">
                        <div class="section-header">
                            <h2>Account Settings</h2>
                        </div>
                        <div class="settings-list">
                            <a href="#" class="setting-item">
                                <i class="fas fa-user"></i>
                                <span>Edit Profile</span>
                            </a>
                            <a href="#" class="setting-item">
                                <i class="fas fa-lock"></i>
                                <span>Change Password</span>
                            </a>
                            <a href="#" class="setting-item">
                                <i class="fas fa-bell"></i>
                                <span>Notification Settings</span>
                            </a>
                            <a href="logout.php" class="setting-item text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 