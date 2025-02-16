<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch cart items
$sql = "SELECT c.quantity, p.* 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($item = $result->fetch_assoc()) {
    $cart_items[] = $item;
    $total += $item['price'] * $item['quantity'];
}

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        $stmt->bind_param("id", $user_id, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Add order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($cart_items as $item) {
            // Check stock
            $stock_check = $conn->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
            $stock_check->bind_param("i", $item['id']);
            $stock_check->execute();
            $stock_result = $stock_check->get_result();
            $current_stock = $stock_result->fetch_assoc()['stock'];
            
            if ($current_stock < $item['quantity']) {
                throw new Exception("Insufficient stock for " . $item['name']);
            }
            
            // Update stock
            $new_stock = $current_stock - $item['quantity'];
            $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $update_stock->bind_param("ii", $new_stock, $item['id']);
            $update_stock->execute();
            
            // Add order item
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            // Check if this is a subscription product (contains duration in name)
            if (preg_match('/(\d+)\s*(Month|Day)s?/i', $item['name'], $matches)) {
                $duration = $matches[1];
                $unit = strtolower($matches[2]);
                
                // Calculate subscription dates
                $start_date = new DateTime();
                $end_date = clone $start_date;
                
                // Add duration based on unit
                if ($unit == 'month') {
                    $end_date->modify("+$duration months");
                } else {
                    $end_date->modify("+$duration days");
                }
                
                // Check for existing active subscription
                $check_sql = "SELECT id, end_date FROM subscriptions 
                             WHERE user_id = ? AND product_id = ? 
                             AND status = 'active' AND end_date > NOW()";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $user_id, $item['id']);
                $check_stmt->execute();
                $existing_sub = $check_stmt->get_result()->fetch_assoc();
                
                if ($existing_sub) {
                    // Extend existing subscription
                    $new_end = new DateTime($existing_sub['end_date']);
                    if ($unit == 'month') {
                        $new_end->modify("+$duration months");
                    } else {
                        $new_end->modify("+$duration days");
                    }
                    
                    $update_sql = "UPDATE subscriptions SET end_date = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $new_end_str = $new_end->format('Y-m-d H:i:s');
                    $update_stmt->bind_param("si", $new_end_str, $existing_sub['id']);
                    $update_stmt->execute();
                } else {
                    // Create new subscription
                    $sub_sql = "INSERT INTO subscriptions (user_id, product_id, start_date, end_date) 
                               VALUES (?, ?, ?, ?)";
                    $sub_stmt = $conn->prepare($sub_sql);
                    $start = $start_date->format('Y-m-d H:i:s');
                    $end = $end_date->format('Y-m-d H:i:s');
                    $sub_stmt->bind_param("iiss", $user_id, $item['id'], $start, $end);
                    $sub_stmt->execute();
                }
            }
        }
        
        // Clear cart
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart->bind_param("i", $user_id);
        $clear_cart->execute();
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Order placed successfully! Your order ID is #" . $order_id;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-gamepad me-2 text-primary"></i>TechGamer
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
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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
        <div class="checkout-container">
            <h1 class="mb-4">Checkout</h1>
            
            <?php if($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <?php if($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <h3>Order Summary</h3>
                        <div class="order-summary">
                            <?php foreach($cart_items as $item): ?>
                                <div class="order-item">
                                    <?php if(strpos($item['image_url'], 'fas ') !== false): ?>
                                        <div class="product-icon">
                                            <i class="fas fa-gamepad"></i>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?php echo $item['image_url'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="product-image">
                                    <?php endif; ?>
                                    <div class="product-info">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p>Quantity: <?php echo $item['quantity']; ?> x ₱<?php echo number_format($item['price'], 2); ?></p>
                                    </div>
                                    <div class="ms-auto">
                                        <strong>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="total-section">
                                <div class="d-flex justify-content-between">
                                    <h4>Total:</h4>
                                    <h4>₱<?php echo number_format($total, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <h3>Payment Details</h3>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="expiry" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry" placeholder="MM/YY" required>
                                </div>
                                <div class="col">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <button type="submit" class="btn btn-place-order">
                                <i class="fas fa-lock me-2"></i>Place Order
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

 