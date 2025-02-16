<?php
require_once 'config/database.php';
session_start();

// Check for welcome message
$welcomeMessage = '';
if(isset($_SESSION['welcome_back']) && $_SESSION['welcome_back']) {
    $welcomeMessage = "Welcome back, " . htmlspecialchars($_SESSION['username']) . "!";
    unset($_SESSION['welcome_back']); // Clear the message after showing
}

// Fetch products from database
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechGamer - Premium Gaming Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <!-- Navigation Bar -->
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
                        <a class="nav-link active" href="index.php">
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
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user-circle"></i>
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="subscriptions.php">
                                        <i class="fas fa-clock"></i>
                                        <span>Subscriptions</span>
                                        <?php
                                        // Get active subscriptions count
                                        $sub_sql = "SELECT COUNT(*) as count FROM subscriptions 
                                                   WHERE user_id = ? AND status = 'active' 
                                                   AND end_date > NOW()";
                                        $stmt = $conn->prepare($sub_sql);
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $sub_count = $stmt->get_result()->fetch_assoc()['count'];
                                        if($sub_count > 0):
                                        ?>
                                            <span class="badge ms-2"><?php echo $sub_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="orders.php">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>Orders</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
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

    <!-- Welcome Message -->
    <?php if($welcomeMessage): ?>
    <div class="welcome-message">
        <div class="container">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $welcomeMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4">Level Up Your Gaming Experience</h1>
            <p class="lead">Discover premium gaming hardware and software solutions</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="mt-4">
                    <a href="register.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i>Join Now
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="products-section">
        <div class="container">
            <h2 class="text-center mb-5">Featured Products</h2>
            <?php if(empty($products)): ?>
                <div class="text-center empty-products">
                    <i class="fas fa-box-open mb-3"></i>
                    <h3>No products available at the moment</h3>
                    <p class="text-muted">Please check back later for our amazing products!</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="product-card">
                                <!-- Software/Hardware Badge -->
                                <div class="product-badge <?php echo strtolower($product['category']); ?>">
                                    <?php echo ucfirst($product['category']); ?>
                                </div>

                                <!-- Payment Method Badge -->
                                <?php if(strpos($product['name'], 'GCash') !== false): ?>
                                    <div class="payment-badge">
                                        <i class="fas fa-wallet me-1"></i> GCash
                                    </div>
                                <?php elseif(strpos($product['name'], 'Crypto') !== false): ?>
                                    <div class="payment-badge">
                                        <i class="fab fa-bitcoin me-1"></i> Crypto
                                    </div>
                                <?php endif; ?>

                                <!-- Product Icon/Image -->
                                <div class="product-icon">
                                    <i class="fas fa-gamepad"></i>
                                </div>

                                <div class="product-details">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                    <div class="product-footer">
                                        <span class="price">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About TechGamer</h5>
                    <p>Your ultimate destination for premium gaming hardware and software solutions.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p>
                        <i class="fas fa-envelope me-2"></i>support@techgamer.com<br>
                        <i class="fas fa-phone me-2"></i>(555) 123-4567
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> TechGamer. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 