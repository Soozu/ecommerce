<?php
require_once 'config/database.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Enhanced Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = '<i class="fas fa-exclamation-circle"></i> Please fill in all fields';
    } 
    // Username validation
    elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = '<i class="fas fa-exclamation-circle"></i> Username must be between 3 and 20 characters';
    }
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = '<i class="fas fa-exclamation-circle"></i> Username can only contain letters, numbers, and underscores';
    }
    // Email validation
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '<i class="fas fa-exclamation-circle"></i> Please enter a valid email address';
    }
    // Password validation
    elseif (strlen($password) < 6) {
        $error = '<i class="fas fa-exclamation-circle"></i> Password must be at least 6 characters long';
    }
    elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = '<i class="fas fa-exclamation-circle"></i> Password must contain both letters and numbers';
    }
    elseif ($password !== $confirm_password) {
        $error = '<i class="fas fa-exclamation-circle"></i> Passwords do not match';
    }
    else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = '<i class="fas fa-exclamation-circle"></i> Username is already taken';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = '<i class="fas fa-exclamation-circle"></i> Email is already registered';
            } else {
                // Create new user with enhanced security
                try {
                    // Start transaction
                    $conn->begin_transaction();

                    // Hash password with strong algorithm
                    $hashed_password = password_hash($password, PASSWORD_ARGON2ID, [
                        'memory_cost' => 2048,
                        'time_cost' => 4,
                        'threads' => 3
                    ]);

                    // Insert user
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $username, $email, $hashed_password);
                    
                    if ($stmt->execute()) {
                        // Commit transaction
                        $conn->commit();
                        
                        $success = '
                            <i class="fas fa-check-circle"></i> Registration successful! 
                            <div class="mt-2">
                                <a href="login.php" class="alert-link">Click here to login</a>
                            </div>';
                            
                        // Clear form data
                        $username = $email = '';
                    } else {
                        throw new Exception("Error executing query");
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $error = '<i class="fas fa-exclamation-circle"></i> Registration failed. Please try again later.';
                    // Log the error (in a production environment)
                    error_log("Registration error: " . $e->getMessage());
                }
            }
        }
    }
}

// Add this JavaScript for real-time password validation
$password_validation_script = "
<script>
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const requirements = {
        length: password.length >= 6,
        letters: /[A-Za-z]/.test(password),
        numbers: /[0-9]/.test(password)
    };
    
    // Update requirements visual feedback
    document.getElementById('req-length').style.color = requirements.length ? '#30D158' : '#FF453A';
    document.getElementById('req-letters').style.color = requirements.letters ? '#30D158' : '#FF453A';
    document.getElementById('req-numbers').style.color = requirements.numbers ? '#30D158' : '#FF453A';
});
</script>";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TechGamer Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/register.css">
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
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="register-container">
            <div class="register-header">
                <i class="fas fa-gamepad"></i>
                <h2>Join The Elite</h2>
                <p class="text-muted">Create your gaming account</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
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
                            <input type="text" class="form-control" id="username" name="username" required
                                   placeholder="Choose your username">
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="Create your password">
                            <div class="password-requirements">
                                <p><i class="fas fa-check-circle" id="req-length"></i> At least 6 characters</p>
                                <p><i class="fas fa-check-circle" id="req-letters"></i> Contains letters</p>
                                <p><i class="fas fa-check-circle" id="req-numbers"></i> Contains numbers</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-shield-alt"></i>
                                Confirm Password
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                   placeholder="Confirm your password">
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-3 mt-4">
                    <button type="submit" class="btn btn-primary btn-register">
                        <i class="fas fa-user-plus me-2"></i>
                        Create Account
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <p class="mb-0">Already have an account? 
                        <a href="login.php" class="fw-bold">
                            Login here <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $password_validation_script; ?>
</body>
</html> 