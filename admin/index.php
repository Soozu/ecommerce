<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$page_title = 'Dashboard';
$current_page = 'dashboard';

// Fetch statistics
// Total Users
$users_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$users_result = $conn->query($users_sql);
$total_users = $users_result->fetch_assoc()['total'];

// Total Orders
$orders_sql = "SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM orders";
$orders_result = $conn->query($orders_sql);
$orders_data = $orders_result->fetch_assoc();
$total_orders = $orders_data['total'];
$total_revenue = $orders_data['revenue'] ?? 0;

// Active Subscriptions
$subs_sql = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'active' AND end_date > NOW()";
$subs_result = $conn->query($subs_sql);
$active_subscriptions = $subs_result->fetch_assoc()['total'];

// Recent Orders
$recent_orders_sql = "SELECT o.*, u.username, COUNT(oi.id) as items_count 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     LEFT JOIN order_items oi ON o.id = oi.order_id 
                     GROUP BY o.id 
                     ORDER BY o.created_at DESC 
                     LIMIT 5";
$recent_orders = $conn->query($recent_orders_sql)->fetch_all(MYSQLI_ASSOC);

// Monthly Revenue Chart Data
$monthly_revenue_sql = "SELECT 
                         DATE_FORMAT(created_at, '%Y-%m') as month,
                         SUM(total_amount) as revenue
                       FROM orders
                       WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                       GROUP BY month
                       ORDER BY month ASC";
$monthly_revenue = $conn->query($monthly_revenue_sql)->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo number_format($total_users); ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo number_format($total_orders); ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo number_format($active_subscriptions); ?></h3>
            <p>Active Subscriptions</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-details">
            <h3>₱<?php echo number_format($total_revenue, 2); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Recent Orders</h2>
            <a href="orders/" class="admin-btn admin-btn-primary">View All</a>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo $order['items_count']; ?> items</td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="admin-badge admin-badge-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Revenue Overview</h2>
        </div>
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const monthlyRevenue = <?php echo json_encode($monthly_revenue); ?>;
const labels = monthlyRevenue.map(item => {
    const date = new Date(item.month + '-01');
    return date.toLocaleDateString('default', { month: 'short', year: 'numeric' });
});
const data = monthlyRevenue.map(item => item.revenue);

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Monthly Revenue',
            data: data,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?> 