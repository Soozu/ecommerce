<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <i class="fas fa-shield-alt"></i>
            <span>TechGamer Admin</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <h5 class="nav-section-title">Main</h5>
            <ul class="nav-items">
                <li>
                    <a href="index.php" class="<?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <h5 class="nav-section-title">Management</h5>
            <ul class="nav-items">
                <li>
                    <a href="products.php" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="subscriptions.php" class="<?php echo $current_page == 'subscriptions' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i>
                        <span>Subscriptions</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="<?php echo $current_page == 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="<?php echo $current_page == 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <h5 class="nav-section-title">Reports</h5>
            <ul class="nav-items">
                <li>
                    <a href="reports/sales.php" class="<?php echo $current_page == 'sales' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Sales Report</span>
                    </a>
                </li>
                <li>
                    <a href="reports/subscriptions.php" class="<?php echo $current_page == 'sub_reports' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Subscription Report</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>

<style>
/* Sidebar Styling */
.admin-sidebar {
    width: 260px;
    background: var(--admin-sidebar);
    min-height: 100vh;
    padding: 1.5rem;
    color: white;
}

.sidebar-header {
    margin-bottom: 2rem;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: white;
    text-decoration: none;
    font-size: 1.25rem;
    font-weight: 600;
}

.nav-section {
    margin-bottom: 2rem;
}

.nav-section-title {
    color: #64748b;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}

.nav-items {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-items li {
    margin-bottom: 0.5rem;
}

.nav-items a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #94a3b8;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
}

.nav-items a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-items a.active {
    background: var(--admin-primary);
    color: white;
}

/* Responsive Sidebar */
@media (max-width: 768px) {
    .admin-sidebar {
        position: fixed;
        left: -260px;
        top: 0;
        bottom: 0;
        z-index: 1000;
        transition: left 0.3s ease;
    }

    .admin-sidebar.show {
        left: 0;
    }
}
</style> 