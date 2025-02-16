<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$page_title = 'Subscriptions';
$current_page = 'subscriptions';

// Fetch subscriptions with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$subscriptions_sql = "SELECT s.*, 
                            u.username, 
                            p.name as product_name,
                            p.price
                     FROM subscriptions s
                     JOIN users u ON s.user_id = u.id
                     JOIN products p ON s.product_id = p.id
                     ORDER BY s.created_at DESC
                     LIMIT ? OFFSET ?";

$stmt = $conn->prepare($subscriptions_sql);
$stmt->bind_param('ii', $items_per_page, $offset);
$stmt->execute();
$subscriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total subscriptions count for pagination
$total_sql = "SELECT COUNT(*) as count FROM subscriptions";
$total_subscriptions = $conn->query($total_sql)->fetch_assoc()['count'];
$total_pages = ceil($total_subscriptions / $items_per_page);

include 'includes/header.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h1>Subscriptions</h1>
        <div class="header-actions">
            <a href="reports/subscriptions.php" class="admin-btn admin-btn-secondary">
                <i class="fas fa-chart-line"></i> View Report
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="subscription-filters">
            <div class="filter-group">
                <label for="status-filter">Status:</label>
                <select id="status-filter" class="form-control">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div class="search-group">
                <input type="text" id="search-input" class="form-control" placeholder="Search username...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($subscriptions as $sub): ?>
                        <tr>
                            <td>#<?php echo $sub['id']; ?></td>
                            <td><?php echo htmlspecialchars($sub['username']); ?></td>
                            <td><?php echo htmlspecialchars($sub['product_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($sub['start_date'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($sub['end_date'])); ?></td>
                            <td>
                                <?php 
                                $status = strtotime($sub['end_date']) > time() ? 'active' : 'expired';
                                $statusClass = $status === 'active' ? 'success' : 'danger';
                                ?>
                                <span class="subscription-status status-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button onclick="viewDetails(<?php echo $sub['id']; ?>)" 
                                        class="admin-btn admin-btn-sm admin-btn-info">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if($status === 'active'): ?>
                                    <button onclick="cancelSubscription(<?php echo $sub['id']; ?>)" 
                                            class="admin-btn admin-btn-sm admin-btn-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="admin-btn admin-btn-sm <?php echo $page === $i ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Subscription Details Modal -->
<div class="modal" id="subscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subscription Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('subscriptionModal'));
    
    // Fetch subscription details
    fetch(`subscriptions/details.php?id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.querySelector('#subscriptionModal .modal-body').innerHTML = html;
            modal.show();
        });
}

function cancelSubscription(id) {
    if(confirm('Are you sure you want to cancel this subscription?')) {
        window.location.href = `subscriptions/cancel.php?id=${id}`;
    }
}

// Filter functionality
document.getElementById('status-filter').addEventListener('change', function() {
    filterSubscriptions();
});

document.getElementById('search-input').addEventListener('input', function() {
    filterSubscriptions();
});

function filterSubscriptions() {
    const status = document.getElementById('status-filter').value;
    const search = document.getElementById('search-input').value.toLowerCase();
    
    document.querySelectorAll('.admin-table tbody tr').forEach(row => {
        const rowStatus = row.querySelector('.subscription-status').textContent.toLowerCase();
        const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        
        const statusMatch = !status || rowStatus === status;
        const searchMatch = !search || username.includes(search);
        
        row.style.display = statusMatch && searchMatch ? '' : 'none';
    });
}
</script>

<?php include 'includes/footer.php'; ?> 