<?php
require_once 'config/database.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$product = $result->fetch_assoc();

// Fetch recommended products (same category, excluding current product)
$recommended_sql = "SELECT * FROM products 
                   WHERE category = ? 
                   AND id != ? 
                   ORDER BY RAND() 
                   LIMIT 3";
$stmt = $conn->prepare($recommended_sql);
$stmt->bind_param("si", $product['category'], $product_id);
$stmt->execute();
$recommended_result = $stmt->get_result();
$recommended_products = [];
while($row = $recommended_result->fetch_assoc()) {
    $recommended_products[] = $row;
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($quantity > 0 && $quantity <= $product['stock']) {
        $user_id = $_SESSION['user_id'];
        
        // Check if product already exists in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $cart_result = $stmt->get_result();
        
        if ($cart_result->num_rows > 0) {
            // Update existing cart item
            $cart_item = $cart_result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            if ($new_quantity <= $product['stock']) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                if ($stmt->execute()) {
                    $success_message = 'Cart updated successfully!';
                } else {
                    $error_message = 'Error updating cart. Please try again.';
                }
            } else {
                $error_message = 'Cannot add more items than available in stock.';
            }
        } else {
            // Add new cart item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
            if ($stmt->execute()) {
                $success_message = 'Product added to cart successfully!';
            } else {
                $error_message = 'Error adding to cart. Please try again.';
            }
        }
    } else {
        $error_message = 'Invalid quantity or insufficient stock!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/product.css">
</head>
<body>
    <div class="login-bg">
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-gamepad me-2"></i>TechGamer
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">
                            <i class="fas fa-box me-1"></i>Products
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart me-1"></i>Cart
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user-circle me-2"></i>Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="orders.php">
                                        <i class="fas fa-shopping-bag me-2"></i>Orders
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="product-container">
            <?php if($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <a href="cart.php" class="alert-link ms-2">View Cart</a>
                </div>
            <?php endif; ?>
            
            <?php if($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="product-image-container">
                        <?php if(strpos($product['image_url'], 'fas ') !== false || strpos($product['image_url'], 'fab ') !== false): ?>
                            <i class="product-icon fas fa-gamepad"></i>
                        <?php else: ?>
                            <img src="<?php echo $product['image_url'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 product-details">
                    <h1><?php echo $product['name']; ?></h1>
                    <span class="category-badge badge-<?php echo strtolower($product['category']); ?>">
                        <?php echo ucfirst($product['category']); ?>
                    </span>
                    <div class="product-price">
                        <span class="currency">₱</span>
                        <?php echo number_format($product['price'], 2); ?>
                    </div>
                    <div class="mb-4">
                        <?php echo nl2br($product['description']); ?>
                    </div>
                    
                    <?php if($product['stock'] > 0): ?>
                        <div class="stock text-success mb-4">
                            <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
                        </div>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity:</label>
                                    <input type="number" class="form-control quantity-input" 
                                           id="quantity" name="quantity" 
                                           value="1" min="1" max="<?php echo $product['stock']; ?>">
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Please <a href="login.php">login</a> to add this item to your cart.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="stock text-danger mb-4">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(!empty($recommended_products)): ?>
                <div class="recommended-products">
                    <h2>Recommended Products</h2>
                    <div class="row">
                        <?php foreach($recommended_products as $rec_product): ?>
                            <div class="col-md-4 mb-4">
                                <div class="product-card">
                                    <div class="product-badge <?php echo strtolower($rec_product['category']); ?>">
                                        <?php echo ucfirst($rec_product['category']); ?>
                                    </div>
                                    
                                    <?php if(strpos($rec_product['name'], 'GCash') !== false): ?>
                                        <div class="payment-badge">
                                            <i class="fas fa-wallet me-1"></i> GCash
                                        </div>
                                    <?php elseif(strpos($rec_product['name'], 'Crypto') !== false): ?>
                                        <div class="payment-badge">
                                            <i class="fab fa-bitcoin me-1"></i> Crypto
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="product-icon">
                                        <i class="fas fa-gamepad"></i>
                                    </div>
                                    
                                    <div class="product-details">
                                        <h3><?php echo htmlspecialchars($rec_product['name']); ?></h3>
                                        <p><?php echo htmlspecialchars(substr($rec_product['description'], 0, 100)) . '...'; ?></p>
                                        <div class="product-footer">
                                            <span class="price">₱<?php echo number_format($rec_product['price'], 2); ?></span>
                                            <a href="product.php?id=<?php echo $rec_product['id']; ?>" 
                                               class="btn btn-primary">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 