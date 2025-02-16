<?php
require_once '../../config/database.php';
require_once '../includes/auth.php';

$page_title = 'Edit Product';
$current_page = 'products';
$admin_path = '..';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: ../products.php');
    exit;
}

// Fetch product
$product_sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: ../products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 1;
    $stock = $_POST['stock'] ?? 0;
    $status = $_POST['status'] ?? 'active';

    $sql = "UPDATE products 
            SET name = ?, description = ?, price = ?, 
                category_id = ?, stock = ?, status = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdiisi', $name, $description, $price, 
                      $category_id, $stock, $status, $id);
    
    if ($stmt->execute()) {
        header('Location: ../products.php?success=Product updated successfully');
        exit;
    } else {
        $error = "Error updating product: " . $conn->error;
    }
}

// Fetch categories for dropdown
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($categories_sql)->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="admin-content">
    <div class="admin-content-header">
        <h1>Edit Product</h1>
        <a href="../products.php" class="admin-btn admin-btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="admin-card">
        <form method="POST" class="admin-form">
            <div class="admin-form-row">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" 
                          rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="admin-form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" class="form-control" 
                           step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" class="form-control" 
                           min="0" value="<?php echo $product['stock']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="draft" <?php echo $product['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
            </div>

            <div class="admin-form-buttons">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-save"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 