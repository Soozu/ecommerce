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

// Handle subscription cancellation if requested
if (isset($_POST['cancel_subscription']) && isset($_POST['subscription_id'])) {
    $sub_id = $_POST['subscription_id'];
    
    // Verify subscription belongs to user
    $verify_sql = "SELECT id FROM subscriptions WHERE id = ? AND user_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $sub_id, $user_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows > 0) {
        // Update subscription status to expired
        $update_sql = "UPDATE subscriptions SET status = 'expired' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $sub_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Subscription cancelled successfully.";
        } else {
            $error_message = "Error cancelling subscription. Please try again.";
        }
    }
}

// Fetch active subscriptions with product details
$active_sql = "SELECT s.*, p.name as product_name, p.description, p.price 
               FROM subscriptions s 
               JOIN products p ON s.product_id = p.id 
               WHERE s.user_id = ? 
               AND s.status = 'active' 
               AND s.end_date > NOW()
               ORDER BY s.end_date ASC";

$stmt = $conn->prepare($active_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$active_subscriptions = [];
while ($row = $result->fetch_assoc()) {
    $active_subscriptions[] = $row;
}

// Fetch expired or cancelled subscriptions
$expired_sql = "SELECT s.*, p.name as product_name, p.price 
                FROM subscriptions s 
                JOIN products p ON s.product_id = p.id 
                WHERE s.user_id = ? 
                AND (s.status = 'expired' OR s.end_date <= NOW())
                ORDER BY s.end_date DESC 
                LIMIT 5"; // Show only last 5 expired subscriptions

$stmt = $conn->prepare($expired_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$expired_subscriptions = [];
while ($row = $result->fetch_assoc()) {
    $expired_subscriptions[] = $row;
}

// Add auto-update for expired subscriptions
$update_expired = "UPDATE subscriptions 
                  SET status = 'expired' 
                  WHERE user_id = ? 
                  AND status = 'active' 
                  AND end_date <= NOW()";
$stmt = $conn->prepare($update_expired);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Calculate subscription statistics
$stats_sql = "SELECT 
                COUNT(CASE WHEN status = 'active' AND end_date > NOW() THEN 1 END) as active_count,
                COUNT(CASE WHEN status = 'expired' OR end_date <= NOW() THEN 1 END) as expired_count,
                SUM(CASE WHEN status = 'active' AND end_date > NOW() THEN 1 ELSE 0 END) as total_active
              FROM subscriptions 
              WHERE user_id = ?";
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subscriptions - TechGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/subscriptions.css">
</head>
<body>
    <!-- Add this right after <body> -->
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
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user-circle"></i>
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item active" href="subscriptions.php">
                                        <i class="fas fa-clock"></i>
                                        <span>Subscriptions</span>
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
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="subscription-bg">
        <div class="container">
            <div class="subscriptions-container">
                <h1>My Subscriptions</h1>
                
                <?php if(empty($active_subscriptions) && empty($expired_subscriptions)): ?>
                    <div class="empty-subscriptions">
                        <i class="fas fa-clock"></i>
                        <h3>No Subscriptions Found</h3>
                        <p>You don't have any active or expired subscriptions.</p>
                        <a href="index.php" class="btn btn-primary">Browse Products</a>
                    </div>
                <?php else: ?>
                    <!-- Active Subscriptions -->
                    <?php if(!empty($active_subscriptions)): ?>
                        <h2>Active Subscriptions</h2>
                        <div class="subscription-grid">
                            <?php foreach($active_subscriptions as $sub): ?>
                                <div class="subscription-card">
                                    <div class="subscription-header">
                                        <div class="subscription-icon">
                                            <i class="fas fa-gamepad"></i>
                                        </div>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                    
                                    <div class="subscription-content">
                                        <h3><?php echo htmlspecialchars($sub['product_name']); ?></h3>
                                        
                                        <div class="subscription-info">
                                            <div class="info-item">
                                                <i class="fas fa-calendar"></i>
                                                <span>Expires: <?php echo date('F j, Y', strtotime($sub['end_date'])); ?></span>
                                            </div>
                                            
                                            <div class="info-item">
                                                <i class="fas fa-clock"></i>
                                                <span>
                                                    <?php
                                                    $now = new DateTime();
                                                    $end = new DateTime($sub['end_date']);
                                                    $diff = $now->diff($end);
                                                    if($diff->days > 0) {
                                                        echo $diff->days . ' days remaining';
                                                    } else {
                                                        echo 'Expires today';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <?php if($sub['status'] == 'active'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <button type="submit" name="cancel_subscription" class="btn btn-danger">
                                                    <i class="fas fa-times-circle"></i> Cancel Subscription
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Expired Subscriptions -->
                    <?php if(!empty($expired_subscriptions)): ?>
                        <h2>Expired Subscriptions</h2>
                        <div class="subscription-list expired">
                            <?php foreach($expired_subscriptions as $sub): ?>
                                <div class="subscription-card expired">
                                    <div class="subscription-icon">
                                        <i class="fas fa-gamepad"></i>
                                    </div>
                                    <div class="subscription-details">
                                        <h3><?php echo htmlspecialchars($sub['product_name']); ?></h3>
                                        <p class="subscription-status">
                                            <span class="badge bg-secondary">Expired</span>
                                        </p>
                                        <p class="expiry-date">
                                            Expired on: <?php echo date('F j, Y', strtotime($sub['end_date'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Add this before closing body tag -->
    <div class="modal-backdrop"></div>
    <div class="confirmation-modal">
        <h4>Cancel Subscription</h4>
        <p>Are you sure you want to cancel this subscription? This action cannot be undone.</p>
        <div class="modal-buttons">
            <button class="btn btn-modal btn-modal-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn btn-modal btn-modal-confirm" onclick="confirmCancellation()">Confirm</button>
        </div>
    </div>

    <!-- Add this before closing body tag -->
    <script>
    let activeForm = null;
    const modal = document.querySelector('.confirmation-modal');
    const backdrop = document.querySelector('.modal-backdrop');

    // Update the form submission to show modal instead
    document.querySelectorAll('.subscription-card form').forEach(form => {
        form.onsubmit = (e) => {
            e.preventDefault();
            activeForm = form;
            showModal();
        };
    });

    function showModal() {
        backdrop.style.display = 'block';
        modal.style.display = 'block';
        setTimeout(() => {
            backdrop.classList.add('fade-in');
            modal.classList.add('fade-in');
        }, 10);
    }

    function closeModal() {
        backdrop.classList.add('fade-out');
        modal.classList.add('fade-out');
        setTimeout(() => {
            backdrop.style.display = 'none';
            modal.style.display = 'none';
            backdrop.classList.remove('fade-in', 'fade-out');
            modal.classList.remove('fade-in', 'fade-out');
        }, 300);
    }

    function confirmCancellation() {
        if (activeForm) {
            activeForm.submit();
        }
        closeModal();
    }

    // Close modal when clicking outside
    backdrop.onclick = closeModal;

    // Prevent modal close when clicking inside modal
    modal.onclick = (e) => e.stopPropagation();
    </script>
</body>
</html> 