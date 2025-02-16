<?php 
$css_path = isset($admin_path) ? $admin_path . '/assets/css' : 'assets/css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - TechGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>/admin.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>/dashboard.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>/components.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>/products.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>/subscriptions.css">
</head>
<body class="admin-theme">
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <nav class="admin-nav">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="admin-nav-right">
                    <div class="admin-notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    
                    <div class="admin-profile dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($admin_username); ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="settings/">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <div class="admin-content"> 