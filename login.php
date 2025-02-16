<?php
require_once 'config/database.php';
session_start();

$error = '';
$username = '';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Enhanced Validation
    if (empty($username) || empty($password)) {
        $error = '<i class="fas fa-exclamation-circle"></i> Please fill in all fields';
    } 
    else {
        try {
            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT id, username, email, password, login_attempts, last_attempt 
                                  FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Check for too many login attempts
                if ($user['login_attempts'] >= 5 && time() - strtotime($user['last_attempt']) < 900) {
                    $timeLeft = ceil((900 - (time() - strtotime($user['last_attempt']))) / 60);
                    $error = '<i class="fas fa-lock"></i> Account temporarily locked. Please try again in ' . $timeLeft . ' minutes';
                }
                else {
                    if (password_verify($password, $user['password'])) {
                        // Reset login attempts on successful login
                        $resetAttempts = $conn->prepare("UPDATE users SET login_attempts = 0 WHERE id = ?");
                        $resetAttempts->bind_param("i", $user['id']);
                        $resetAttempts->execute();
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        
                        // Handle Remember Me
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                            
                            $storeToken = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                            $storeToken->bind_param("si", $hashedToken, $user['id']);
                            $storeToken->execute();
                            
                            setcookie('remember_token', $token, time() + (86400 * 30), '/');
                        }
                        
                        $_SESSION['welcome_back'] = true;
                        header("Location: index.php");
                        exit();
                    } else {
                        // Increment login attempts
                        $updateAttempts = $conn->prepare("UPDATE users SET 
                            login_attempts = login_attempts + 1,
                            last_attempt = CURRENT_TIMESTAMP 
                            WHERE id = ?");
                        $updateAttempts->bind_param("i", $user['id']);
                        $updateAttempts->execute();
                        
                        $remainingAttempts = 5 - ($user['login_attempts'] + 1);
                        if ($remainingAttempts > 0) {
                            $error = '<i class="fas fa-exclamation-circle"></i> Invalid password. ' . $remainingAttempts . ' attempts remaining';
                        } else {
                            $error = '<i class="fas fa-lock"></i> Too many failed attempts. Account locked for 15 minutes';
                        }
                    }
                }
            } else {
                $error = '<i class="fas fa-exclamation-circle"></i> Invalid username or password';
            }
        } catch (Exception $e) {
            $error = '<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again later';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Update the validation script for username
$validation_script = "
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    let error = false;
    
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    if (!username) {
        document.getElementById('username').classList.add('is-invalid');
        error = true;
    }
    
    if (!password) {
        document.getElementById('password').classList.add('is-invalid');
        error = true;
    }
    
    if (error) {
        e.preventDefault();
    }
});
</script>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TechGamer Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
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
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-gamepad"></i>
                <h2>Tech Gamer</h2>
                <p class="text-muted">Level up your gaming experience</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($username); ?>" required 
                                   placeholder="Enter your username">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="Enter your password">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="#" class="forgot-password">Forgot Password?</a>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login to Dashboard
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <p class="mb-0">New to Tech Gamer? 
                        <a href="register.php" class="fw-bold">
                            Join the squad <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 