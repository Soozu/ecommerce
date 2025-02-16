# PHP E-commerce Platform

A robust e-commerce platform built with PHP, MySQL, and HTML/CSS that provides essential features for running an online store.

## Features

- 🛍️ Product Management
- 🛒 Shopping Cart Functionality
- 💳 Secure Checkout Process
- 👤 User Authentication & Profiles
- 📦 Order Management
- 📝 User Registration
- 🔐 Secure Login/Logout System
- ⚙️ Admin Dashboard
- 💰 Subscription Management

## Directory Structure

```
├── admin/           # Admin panel files
├── assets/          # Static assets (CSS, JS, images)
├── config/          # Configuration files
├── .gitattributes  # Git attributes file
├── cart.php        # Shopping cart functionality
├── checkout.php    # Checkout process
├── index.php       # Main entry point
├── login.php       # User authentication
├── logout.php      # Session termination
├── orders.php      # Order management
├── product.php     # Product display and management
├── profile.php     # User profile management
├── register.php    # New user registration
└── subscriptions.php # Subscription management
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
```

2. Import the database schema:
```bash
mysql -u [username] -p [database_name] < ecommerce_db.sql
```

3. Configure your database connection in `config/` directory

4. Ensure proper permissions are set:
```bash
chmod 755 -R /path/to/project
chmod 777 -R /path/to/project/assets
```

5. Access the application through your web browser

## Usage

1. **Admin Access**
   - Navigate to `/admin`
   - Login with administrator credentials
   - Manage products, orders, and users

2. **Customer Usage**
   - Browse products on the homepage
   - Add items to cart
   - Complete checkout process
   - View order history
   - Manage profile

## Security Features

- Password hashing
- SQL injection prevention
- XSS protection
- CSRF protection
- Secure session management

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the repository or contact the development team. 