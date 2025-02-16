<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

$page_title = 'Products';
$current_page = 'products';

// Fetch products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$products_sql = "SELECT p.*, COALESCE(c.name, p.category) as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param('ii', $items_per_page, $offset);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total products count for pagination
$total_sql = "SELECT COUNT(*) as count FROM products";
$total_products = $conn->query($total_sql)->fetch_assoc()['count'];
$total_pages = ceil($total_products / $items_per_page);

include 'includes/header.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h1>Products</h1>
        <a href="products/add.php" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $product): ?>
                        <tr>
                            <td>#<?php echo $product['id']; ?></td>
                            <td>
                                <div class="product-icon <?php echo strtolower($product['category'] ?? 'software'); ?>">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td>
                                <?php 
                                $status = $product['status'] ?? 'active';  // Default to active if status is not set
                                $statusClass = strtolower($status);
                                ?>
                                <span class="admin-badge admin-badge-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="products/edit.php?id=<?php echo $product['id']; ?>" 
                                   class="admin-btn admin-btn-sm admin-btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteProduct(<?php echo $product['id']; ?>)" 
                                        class="admin-btn admin-btn-sm admin-btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
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

<script>
function deleteProduct(productId) {
    if(confirm('Are you sure you want to delete this product?')) {
        window.location.href = `products/delete.php?id=${productId}`;
    }
}
</script>

<?php include 'includes/footer.php'; ?> 