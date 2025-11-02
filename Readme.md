# MoonHeritage - Hotel Booking Website

A complete hotel booking and management system built with HTML, CSS, JavaScript, Tailwind CSS, PHP, and MySQL.

## Features

✅ User Registration & Authentication
✅ Hotel Search & Filtering
✅ Advanced Booking System
✅ Wishlist Functionality
✅ Review & Rating System
✅ Newsletter Subscription
✅ Payment Integration Ready
✅ Admin Dashboard (Coming Soon)
✅ Responsive Design
✅ Email Notifications
✅ Activity Logging

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript, Tailwind CSS
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: XAMPP (Apache + MySQL)
- **Icons**: Font Awesome 6.4.0
- **Email**: PHP Mail / SMTP

## Installation Guide

### Prerequisites

- XAMPP (or similar Apache + MySQL + PHP environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install XAMPP to `C:\xampp` (Windows) or `/opt/lampp` (Linux)
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project Files

1. Copy all project files to `C:\xampp\htdocs\moonheritage\`
2. Your directory structure should look like:

```
moonheritage/
├── api/
│   ├── wishlist.php
│   └── newsletter.php
├── css/
│   └── style.css
├── images/
│   └── (hotel images)
├── includes/
│   └── footer.php
├── js/
│   └── main.js
├── uploads/
│   └── (upload directory)
├── config.php
├── index.html
├── login.php
├── signup.php
├── hotels.php
├── hotel-details.php
├── booking.php
├── logout.php
├── database.sql
└── README.md
```

### Step 3: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `moonheritage`
3. Import the database:
   - Click on the `moonheritage` database
   - Go to "Import" tab
   - Choose `database.sql` file
   - Click "Go" to import

### Step 4: Configure Database Connection

Edit `config.php` and update these lines if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'moonheritage');
```

### Step 5: Configure Site Settings

In `config.php`, update these settings:

```php
define('SITE_URL', 'http://localhost/moonheritage/');
define('SITE_EMAIL', 'your-email@example.com');
```

### Step 6: Set Up Email (Optional)

For email functionality, configure SMTP settings in `config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

**Note**: For Gmail, you need to create an "App Password" from Google Account settings.

### Step 7: Create Upload Directories

Create these directories with write permissions:

```bash
moonheritage/uploads/
moonheritage/uploads/hotels/
moonheritage/uploads/users/
moonheritage/uploads/promos/
```

On Windows, right-click > Properties > Security > Edit > Add write permissions

### Step 8: Access the Website

Open your browser and go to:
- **Homepage**: `http://localhost/moonheritage/`
- **Admin Login**: `http://localhost/moonheritage/login.php`

## Default Login Credentials

### Admin Account
- **Email**: admin@moonheritage.com
- **Password**: admin123

**⚠️ Important**: Change the admin password after first login!

## Sample Data

The database includes:
- 8 sample hotels
- 20 amenities
- 2 sample promotions
- 6 destinations
- 1 admin user

## Configuration Options

### Change Password Requirements

In `config.php`:
```php
define('PASSWORD_MIN_LENGTH', 8); // Change minimum password length
```

### Change Session Lifetime

In `config.php`:
```php
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
```

### Change Items Per Page

In `config.php`:
```php
define('HOTELS_PER_PAGE', 12); // Hotels per page
```

## Troubleshooting

### Database Connection Error
- Check if MySQL service is running in XAMPP
- Verify database credentials in `config.php`
- Ensure database `moonheritage` exists

### Images Not Loading
- Check if images exist in `images/` and `uploads/` directories
- Verify `SITE_URL` is correctly set in `config.php`

### Email Not Sending
- Check SMTP configuration in `config.php`
- For Gmail, enable "Less secure app access" or use App Password
- Check spam folder for emails

### Permission Denied Errors
- Ensure `uploads/` directory has write permissions
- On Linux: `chmod -R 755 uploads/`

### Session Issues
- Clear browser cookies
- Check if session directory is writable
- Restart Apache server

## Directory Permissions (Linux)

```bash
chmod -R 755 moonheritage/
chmod -R 777 moonheritage/uploads/
```

## Security Recommendations

1. **Change Default Admin Password**
2. **Update Encryption Key** in `config.php`
3. **Enable HTTPS** for production
4. **Set `display_errors` to 0** in production
5. **Use strong database password**
6. **Regular backups** of database
7. **Keep PHP and MySQL updated**

## API Endpoints

### Wishlist API
- **Endpoint**: `/api/wishlist.php`
- **Method**: POST
- **Auth**: Required
- **Body**: `{ "hotel_id": 123 }`

### Newsletter API
- **Endpoint**: `/api/newsletter.php`
- **Method**: POST
- **Body**: `{ "email": "user@example.com" }`

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Opera (latest)

## File Structure Explained

- **config.php**: Database and site configuration
- **index.html**: Homepage
- **login.php**: User login page
- **signup.php**: User registration
- **hotels.php**: Hotel listing with filters
- **hotel-details.php**: Individual hotel details
- **booking.php**: Booking checkout
- **logout.php**: Logout functionality
- **api/**: API endpoints for AJAX requests
- **includes/**: Reusable components (footer, header)
- **css/**: Custom stylesheets
- **js/**: JavaScript files
- **images/**: Static images
- **uploads/**: User uploaded content

## Development Notes

- Uses PDO for database operations (SQL injection protection)
- CSRF token validation on forms
- XSS protection with htmlspecialchars
- Password hashing with bcrypt
- Session security measures
- Activity logging system

## Future Enhancements

- [ ] Admin Dashboard
- [ ] User Profile Management
- [ ] Booking Management
- [ ] Review Management
- [ ] Payment Gateway Integration
- [ ] Multi-language Support
- [ ] Mobile App
- [ ] Advanced Analytics
- [ ] Chat Support

## Support

For issues or questions:
- Check the troubleshooting section
- Review PHP error logs: `C:\xampp\apache\logs\error.log`
- Check MySQL logs in phpMyAdmin

## License

This project is for educational purposes.

## Credits

- Font Awesome for icons
- Tailwind CSS for styling
- XAMPP for development environment

---


Last Updated: October 2025