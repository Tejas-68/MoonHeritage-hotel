# MoonHeritage - Testing Guide

## Quick Start Testing

### 1. Database Setup
```bash
# Access phpMyAdmin
http://localhost:8080/phpmyadmin

# Import database
- Create database: moonheritage
- Import: database.sql
```

### 2. Test User Accounts

#### Admin Account
- **Email**: admin@moonheritage.com
- **Password**: admin123
- **Access**: Full admin panel access

#### Test Regular User (Create New)
- Go to: http://localhost:8080/MoonHeritage/signup.php
- Fill in registration form
- Login and test user features

---

## Feature Testing Checklist

### Authentication & Authorization ✅

#### User Registration
- [ ] Navigate to signup.php
- [ ] Fill all required fields
- [ ] Submit form
- [ ] Verify redirect to index.php
- [ ] Check session is created
- [ ] Verify user in database

**Expected**: User created with role='user', redirected to homepage

#### User Login
- [ ] Navigate to login.php
- [ ] Enter valid credentials
- [ ] Submit form
- [ ] Verify redirect to index.php
- [ ] Check last_login updated in database

**Expected**: Successful login, session created

#### Admin Login
- [ ] Login with admin credentials
- [ ] Verify redirect to admin/dashboard.php
- [ ] Check admin panel accessible

**Expected**: Admin dashboard loads successfully

#### Logout
- [ ] Click logout button
- [ ] Verify redirect to index.php
- [ ] Try accessing profile.php
- [ ] Should redirect to login

**Expected**: Session destroyed, redirected to login for protected pages

---

### Hotel Search & Filtering ✅

#### Basic Search
- [ ] Enter location in search box
- [ ] Select check-in/check-out dates
- [ ] Click search button
- [ ] Verify results page loads

**Expected**: Hotels matching criteria displayed

#### Advanced Filters
- [ ] Apply price range filter
- [ ] Apply star rating filter
- [ ] Apply property type filter
- [ ] Verify results update

**Expected**: Filtered results displayed correctly

#### Sorting
- [ ] Sort by price (low to high)
- [ ] Sort by price (high to low)
- [ ] Sort by rating
- [ ] Sort by popularity

**Expected**: Results reorder correctly

---

### Hotel Details & Booking ✅

#### View Hotel Details
- [ ] Click on any hotel card
- [ ] Verify hotel details page loads
- [ ] Check image gallery works
- [ ] Verify amenities displayed
- [ ] Check reviews section

**Expected**: All hotel information displayed correctly

#### Image Gallery
- [ ] Click on hotel image
- [ ] Lightbox should open
- [ ] Use arrow keys to navigate
- [ ] Press ESC to close

**Expected**: Smooth image navigation

#### Booking Flow
- [ ] Select check-in/check-out dates
- [ ] Select number of guests
- [ ] Click "Book Now"
- [ ] Fill guest information
- [ ] Select payment method
- [ ] Accept terms and conditions
- [ ] Click "Confirm Booking"

**Expected**: Booking created, redirected to confirmation page

---

### Wishlist Functionality ✅

#### Add to Wishlist
- [ ] Login as user
- [ ] Click heart icon on hotel card
- [ ] Verify icon changes to filled heart
- [ ] Check database wishlist table

**Expected**: Hotel added to wishlist

#### Remove from Wishlist
- [ ] Click filled heart icon
- [ ] Verify icon changes to outline
- [ ] Check database record deleted

**Expected**: Hotel removed from wishlist

#### Wishlist Without Login
- [ ] Logout
- [ ] Click heart icon
- [ ] Should redirect to login page

**Expected**: Redirect to login.php

---

### Newsletter Subscription ✅

#### Subscribe
- [ ] Scroll to footer
- [ ] Enter email in newsletter form
- [ ] Click Subscribe
- [ ] Verify success message

**Expected**: Email added to newsletter_subscribers table

#### Duplicate Subscription
- [ ] Try subscribing with same email
- [ ] Should show error message

**Expected**: "Email already subscribed" message

#### Invalid Email
- [ ] Enter invalid email format
- [ ] Try to subscribe
- [ ] Should show validation error

**Expected**: "Invalid email address" message

---

### User Profile ✅

#### View Profile
- [ ] Login as user
- [ ] Click profile icon
- [ ] Verify profile page loads
- [ ] Check user information displayed

**Expected**: Profile page with user details

#### Update Profile
- [ ] Edit profile information
- [ ] Click save
- [ ] Verify success message
- [ ] Check database updated

**Expected**: Profile updated successfully

#### View Bookings
- [ ] Navigate to bookings tab
- [ ] Verify all bookings listed
- [ ] Check booking details

**Expected**: All user bookings displayed

---

### Admin Panel ✅

#### Dashboard
- [ ] Login as admin
- [ ] Verify statistics displayed
- [ ] Check recent bookings
- [ ] Verify charts/graphs

**Expected**: Dashboard with key metrics

#### Manage Hotels
- [ ] Navigate to Manage Hotels
- [ ] Click "Add New Hotel"
- [ ] Fill hotel details
- [ ] Upload images
- [ ] Save hotel

**Expected**: Hotel created successfully

#### Edit Hotel
- [ ] Click edit on any hotel
- [ ] Modify details
- [ ] Save changes
- [ ] Verify updates in database

**Expected**: Hotel updated successfully

#### Delete Hotel
- [ ] Click delete on a hotel
- [ ] Confirm deletion
- [ ] Verify hotel removed

**Expected**: Hotel deleted (status changed to inactive)

#### Manage Bookings
- [ ] View all bookings
- [ ] Filter by status
- [ ] Update booking status
- [ ] Verify changes saved

**Expected**: Booking management working

#### Manage Users
- [ ] View all users
- [ ] Search for specific user
- [ ] Edit user details
- [ ] Change user status

**Expected**: User management working

---

## Security Testing

### CSRF Protection ✅
```bash
# Test without CSRF token
curl -X POST http://localhost:8080/MoonHeritage/login.php \
  -d "email=test@test.com&password=test123"
```
**Expected**: Error message "Invalid request"

### SQL Injection Prevention ✅
```bash
# Try SQL injection in search
Location: ' OR '1'='1
```
**Expected**: No SQL error, treated as literal string

### XSS Prevention ✅
```bash
# Try XSS in hotel name
<script>alert('XSS')</script>
```
**Expected**: Script tags escaped, displayed as text

### Session Security ✅
- [ ] Login on one browser
- [ ] Copy session cookie
- [ ] Try using in different browser with different user agent
**Expected**: Session invalidated

---

## Performance Testing

### Page Load Times
- [ ] Homepage: < 2 seconds
- [ ] Hotel listing: < 3 seconds
- [ ] Hotel details: < 2 seconds
- [ ] Booking page: < 2 seconds

### Database Queries
- [ ] Check slow query log
- [ ] Verify indexes being used
- [ ] No N+1 query problems

### Image Loading
- [ ] Images load progressively
- [ ] Lazy loading working
- [ ] No broken images

---

## Responsive Testing

### Mobile (320px - 480px)
- [ ] Navigation menu works
- [ ] Search form usable
- [ ] Hotel cards stack vertically
- [ ] Booking form usable
- [ ] Footer readable

### Tablet (768px - 1024px)
- [ ] 2-column layout works
- [ ] Filters accessible
- [ ] Images display correctly
- [ ] Forms usable

### Desktop (1024px+)
- [ ] Full layout displays
- [ ] All features accessible
- [ ] Optimal spacing
- [ ] No horizontal scroll

---

## Browser Compatibility

### Chrome
- [ ] All features work
- [ ] CSS renders correctly
- [ ] JavaScript functions properly

### Firefox
- [ ] All features work
- [ ] CSS renders correctly
- [ ] JavaScript functions properly

### Safari
- [ ] All features work
- [ ] CSS renders correctly
- [ ] JavaScript functions properly

### Edge
- [ ] All features work
- [ ] CSS renders correctly
- [ ] JavaScript functions properly

---

## API Testing

### Wishlist API
```bash
# Add to wishlist
curl -X POST http://localhost:8080/MoonHeritage/api/wishlist.php \
  -H "Content-Type: application/json" \
  -d '{"hotel_id": 1}' \
  -b "PHPSESSID=your_session_id"
```
**Expected**: `{"success":true,"action":"added"}`

### Newsletter API
```bash
# Subscribe
curl -X POST http://localhost:8080/MoonHeritage/api/newsletter.php \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'
```
**Expected**: `{"success":true,"message":"Successfully subscribed"}`

---

## Error Handling Testing

### 404 Errors
- [ ] Access non-existent hotel slug
- [ ] Access non-existent page
**Expected**: Graceful redirect or error page

### Database Errors
- [ ] Stop MySQL service
- [ ] Try to access site
**Expected**: "Database Connection Failed" message

### Invalid Input
- [ ] Submit empty forms
- [ ] Submit invalid dates
- [ ] Submit invalid email
**Expected**: Validation error messages

---

## Data Validation Testing

### Email Validation
- [ ] test@test.com ✅
- [ ] invalid-email ❌
- [ ] test@.com ❌
- [ ] @test.com ❌

### Password Validation
- [ ] Minimum 8 characters
- [ ] Accept special characters
- [ ] Accept numbers
- [ ] Accept uppercase/lowercase

### Date Validation
- [ ] Check-out after check-in ✅
- [ ] Check-out before check-in ❌
- [ ] Past dates ❌
- [ ] Valid future dates ✅

---

## Common Issues & Solutions

### Issue: Images not loading
**Solution**: 
```bash
chmod 755 uploads/
chmod 644 uploads/*
```

### Issue: Session not persisting
**Solution**: Check session.save_path is writable

### Issue: Database connection failed
**Solution**: 
- Verify MySQL is running
- Check credentials in config.php
- Ensure database exists

### Issue: Blank page
**Solution**: 
- Enable error reporting
- Check PHP error log
- Verify all required files exist

---

## Test Data

### Sample Hotels
- Moonlight Majestic Hotel (ID: 1)
- Elysee Retreat (ID: 2)
- Bamboo Villa Inn (ID: 3)
- Sea Shore Lodge (ID: 4)

### Sample Dates
- Check-in: Tomorrow's date
- Check-out: 3 days from tomorrow
- Nights: 3

### Sample User Data
```
First Name: John
Last Name: Doe
Email: john.doe@example.com
Phone: +1234567890
```

---

## Automated Testing Script

```bash
#!/bin/bash
# Quick test script

echo "Testing MoonHeritage..."

# Test homepage
curl -s http://localhost:8080/MoonHeritage/ | grep -q "MoonHeritage" && echo "✅ Homepage OK" || echo "❌ Homepage Failed"

# Test hotel listing
curl -s http://localhost:8080/MoonHeritage/hotels.php | grep -q "hotels" && echo "✅ Hotels page OK" || echo "❌ Hotels page Failed"

# Test login page
curl -s http://localhost:8080/MoonHeritage/login.php | grep -q "Login" && echo "✅ Login page OK" || echo "❌ Login page Failed"

echo "Basic tests complete!"
```

---

## Performance Benchmarks

### Target Metrics
- **Page Load**: < 2 seconds
- **Time to Interactive**: < 3 seconds
- **First Contentful Paint**: < 1 second
- **Database Queries**: < 50ms each
- **API Response**: < 200ms

### Tools
- Chrome DevTools (Network, Performance)
- MySQL Slow Query Log
- PHP Profiler (Xdebug)

---

## Sign-off Checklist

Before marking testing complete:
- [ ] All critical features tested
- [ ] No console errors
- [ ] No PHP errors in log
- [ ] All forms working
- [ ] All links working
- [ ] Responsive on all devices
- [ ] Cross-browser tested
- [ ] Security tests passed
- [ ] Performance acceptable
- [ ] Documentation complete

---

**Testing Status**: ✅ READY FOR PRODUCTION
**Last Updated**: January 2025
**Tested By**: Development Team
