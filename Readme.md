# MoonHeritage - Hotel Booking Platform

A modern, feature-rich hotel booking platform built with PHP, MySQL, and Tailwind CSS.

## Features

### User Features
- **User Authentication**: Secure login and registration with password hashing
- **Hotel Search & Filtering**: Search hotels by location, category, price range, and ratings
- **Hotel Details**: View comprehensive hotel information with image galleries
- **Booking System**: Book hotels with date selection and guest management
- **Wishlist**: Save favorite hotels for later
- **User Profile**: Manage personal information and view booking history
- **Reviews**: Read and write hotel reviews
- **Newsletter Subscription**: Stay updated with special offers

### Admin Features
- **Dashboard**: Overview of bookings, revenue, and statistics
- **Hotel Management**: Add, edit, and delete hotels
- **Booking Management**: View and manage all bookings
- **User Management**: Manage user accounts
- **Amenities Management**: Add and manage hotel amenities
- **Reports**: Generate revenue and booking reports

### Technical Features
- **Responsive Design**: Mobile-first design using Tailwind CSS
- **Security**: CSRF protection, SQL injection prevention, password hashing
- **Session Management**: Secure session handling with user agent validation
- **Image Management**: Upload and manage hotel images
- **Pagination**: Efficient data pagination
- **Search Functionality**: Advanced search with multiple filters
- **RESTful API**: JSON API endpoints for wishlist and newsletter

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP (recommended for local development)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   # Project should be in MoonHeritage folder
   ```

2. **Database Setup**
   - Open phpMyAdmin (http://localhost:8080/phpmyadmin)
   - Create a new database named `moonheritage`
   - Import the `database.sql` file

3. **Configuration**
   - The `config.php` file is already configured for local development
   - Default database settings:
     - Host: localhost
     - User: root
     - Password: (empty)
     - Database: moonheritage

4. **File Permissions**
   ```bash
   chmod 755 /Applications/XAMPP/xamppfiles/htdocs/MoonHeritage/uploads
   ```

5. **Access the Application**
   - Frontend: http://localhost:8080/MoonHeritage/
   - Admin Panel: http://localhost:8080/MoonHeritage/admin/dashboard.php

## Default Credentials

### Admin Account
- **Email**: admin@moonheritage.com
- **Password**: admin123

## Project Structure

```
MoonHeritage/
├── admin/              # Admin panel files
│   ├── dashboard.php
│   ├── manage-hotels.php
│   ├── manage-bookings.php
│   └── ...
├── api/                # API endpoints
│   ├── wishlist.php
│   └── newsletter.php
├── css/                # Stylesheets
│   └── style.css
├── images/             # Static images
├── includes/           # Reusable components
│   └── footer.php
├── js/                 # JavaScript files
│   └── main.js
├── uploads/            # User uploaded files
├── config.php          # Configuration and utility functions
├── database.sql        # Database schema and sample data
├── index.php           # Homepage
├── hotels.php          # Hotel listing page
├── hotel-details.php   # Hotel details page
├── booking.php         # Booking page
├── login.php           # Login page
├── signup.php          # Registration page
├── profile.php         # User profile page
└── README.md           # This file
```

## Key Features Explained

### Security
- **Password Hashing**: Uses bcrypt for secure password storage
- **CSRF Protection**: Tokens generated for form submissions
- **SQL Injection Prevention**: Prepared statements for all database queries
- **Session Security**: User agent validation and session regeneration
- **Input Sanitization**: All user inputs are sanitized

### Database Design
- **Normalized Structure**: Efficient database design with proper relationships
- **Foreign Keys**: Referential integrity maintained
- **Indexes**: Optimized queries with proper indexing
- **Full-Text Search**: Fast hotel search capabilities

### User Experience
- **Responsive Design**: Works seamlessly on all devices
- **Image Gallery**: Lightbox for hotel images
- **Real-time Validation**: Client-side form validation
- **Toast Notifications**: User-friendly feedback messages
- **Smooth Animations**: CSS transitions and animations

## Bug Fixes Applied

### Critical Fixes
1. ✅ Fixed duplicate HTML structure in signup.php
2. ✅ Added missing 'role' field in user registration
3. ✅ Implemented CSRF token verification in login
4. ✅ Fixed hardcoded admin redirect path
5. ✅ Corrected footer HTML structure
6. ✅ Added newsletter subscription form
7. ✅ Created missing API endpoints (wishlist, newsletter)
8. ✅ Fixed SQL injection vulnerability in hotels.php
9. ✅ Added last_login tracking
10. ✅ Improved error handling throughout

### Enhancements
- Better footer design with multiple sections
- Improved security with CSRF protection
- Proper API responses with JSON
- Consistent redirect handling
- Enhanced user session management

## API Endpoints

### Wishlist API
**Endpoint**: `/api/wishlist.php`
**Method**: POST
**Authentication**: Required
**Request Body**:
```json
{
  "hotel_id": 1
}
```
**Response**:
```json
{
  "success": true,
  "action": "added|removed",
  "message": "Added to wishlist"
}
```

### Newsletter API
**Endpoint**: `/api/newsletter.php`
**Method**: POST
**Request Body**:
```json
{
  "email": "user@example.com"
}
```
**Response**:
```json
{
  "success": true,
  "message": "Successfully subscribed to newsletter!"
}
```

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Technologies Used
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **CSS Framework**: Tailwind CSS (CDN)
- **Icons**: Font Awesome 6.4.0
- **Server**: Apache (XAMPP)

## Development Notes

### Adding New Hotels
1. Login as admin
2. Navigate to Admin Panel → Manage Hotels
3. Click "Add New Hotel"
4. Fill in hotel details and upload images
5. Set amenities and pricing

### Managing Bookings
1. Users can book hotels from the hotel details page
2. Admins can view all bookings in the admin panel
3. Booking status can be updated by admin

## Troubleshooting

### Database Connection Error
- Check if MySQL is running in XAMPP
- Verify database credentials in config.php
- Ensure database 'moonheritage' exists

### Images Not Loading
- Check if uploads directory has write permissions
- Verify image paths in database
- Ensure images exist in uploads folder

### Session Issues
- Clear browser cookies
- Check if session.save_path is writable
- Restart Apache server

## Future Enhancements
- Payment gateway integration
- Email notifications
- Advanced search filters
- Hotel owner dashboard
- Mobile app
- Multi-language support
- Social media integration

## Credits
**Developer**: Tejas N C
**LinkedIn**: [linkedin.com/in/tejas-n-c](https://www.linkedin.com/in/tejas-n-c)
**GitHub**: [github.com/Tejas-68](https://github.com/Tejas-68)

## License
This project is for educational purposes.

## Support
For issues or questions, please contact: admin@moonheritage.com

---

**Last Updated**: January 2025
**Version**: 1.0.0
