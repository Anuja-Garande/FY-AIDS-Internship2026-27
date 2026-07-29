# BharatYatra — Tourism & Destination Portal
### Incredible India Tourism Website (PHP + MySQL + XAMPP)

A full tourism portal covering 10 Indian states, 40 destinations, hotels, restaurants,
tour packages, bookings, reviews, wishlist, contact form, user accounts, and a full admin panel.

---

## 1. Setup Instructions

### Step 1 — Copy files
Copy the entire `tourism-portal` folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\tourism-portal
```

### Step 2 — Start XAMPP
Open XAMPP Control Panel → Start **Apache** and **MySQL**.

### Step 3 — Import the database
1. Go to `http://localhost/phpmyadmin`
2. Click **Import**
3. Choose `database/tourism_db.sql`
4. Click **Go**

This creates the `tourism_db` database with all tables and sample data (10 states, 40 destinations, hotels, restaurants, 10 tour packages).

**If you already imported the database before this update:** also import
`database/update_v2.sql` the same way (Import → choose file → Go). This adds the
new tables/columns needed for the photo gallery, trending/recently-viewed tracking,
and login lockout — without touching your existing data.

### Step 4 — Set the admin password
The SQL file inserts a placeholder admin row. Run this **once** in your browser to set a working password:
```
http://localhost/tourism-portal/reset_admin_password.php
```
After running it, **delete `reset_admin_password.php`** for security.

Default admin login:
```
Username: admin
Password: admin123
```
Admin panel URL: `http://localhost/tourism-portal/admin/login.php`

### Step 5 — Open the website
```
http://localhost/tourism-portal/index.php
```

---

## 2. Folder Structure

```
tourism-portal/
├── admin/                  Admin panel (protected, requires admin login)
│   ├── includes/           Shared admin header/sidebar & footer
│   ├── login.php / logout.php
│   ├── dashboard.php       Stats overview
│   ├── destinations.php    CRUD: destinations
│   ├── hotels.php          CRUD: hotels
│   ├── restaurants.php     CRUD: restaurants
│   ├── packages.php        CRUD: tour packages
│   ├── bookings.php        View & update booking status
│   ├── reviews.php         Approve / reject / delete reviews
│   ├── contact-messages.php  View & manage contact form submissions
│   └── users.php           View / delete registered users
├── assets/
│   ├── css/style.css       Full site theme (colors, animations, responsive)
│   ├── js/script.js        Navbar scroll, back-to-top, hero typewriter
│   └── images/             Place your destination/state/hero images here
├── config/db.php           Database connection (edit if your MySQL creds differ)
├── database/tourism_db.sql Full schema + sample data — import this in phpMyAdmin
├── includes/               Shared site header, footer, helper functions
├── uploads/                Used by admin image uploads
├── index.php               Homepage
├── destinations.php / destination-details.php
├── hotels.php
├── restaurants.php
├── packages.php / package-details.php
├── login.php / register.php / logout.php
├── dashboard.php           User dashboard
├── my-bookings.php
├── wishlist.php
├── contact.php
├── about.php
└── reset_admin_password.php   Run once, then delete
```

---

## 3. New Features (Latest Update)

| Feature | Where |
|---|---|
| **States management** | Admin Panel → States (add/edit/delete, blocks deleting a state that still has destinations) |
| **Photo gallery per destination** | Admin Panel → Destinations → click the 🖼 icon → upload multiple photos; shown as a lightbox gallery on the destination page |
| **Trending Now** | Auto-calculated from view counts; shown on homepage + destination sidebar |
| **Recently Viewed** | Tracked per visitor session; shown on the destination sidebar |
| **Google Maps embed** | Automatically shown on every destination page (uses the destination's location text, no API key needed) |
| **Admin dashboard charts** | Bookings + revenue trend (bar/line combo) and booking status breakdown (doughnut chart), via Chart.js |
| **Login rate-limiting** | After 5 failed attempts (user or admin login), that username/IP is locked for 15 minutes |
| **Admin profile & password change** | Admin Panel → My Profile |
| **Custom 404 page** | Styled error page for any broken/missing link (`404.php` + `.htaccess`) |
| **Page loading animation** | A short compass-spin loader shows while each page loads |

## 4. Adding Your Own Images
Right now, destination/hotel/state images use placeholder fallback images if the actual file
is missing. To use your own photos:
- Add state cover photos to `assets/images/states/` (filenames listed in the `states` table, e.g. `rajasthan.jpg`)
- Add destination photos to `assets/images/destinations/` (filenames listed in the `destinations` table)
- Or use the **Admin Panel** → Destinations/Hotels/Restaurants/Packages → Edit → **Image Upload** field to upload directly (saved automatically to the right folder)

## 5. Default Test Accounts
- **Admin:** username `admin`, password `admin123` (after running the reset script)
- **User:** Register a new account via `register.php` — no seed user accounts are included so you can test the full signup flow.

## 6. Notes for Your Project Report
- Passwords are hashed using PHP's `password_hash()` (bcrypt) — matches the SRS security requirement (FR-1, Section 5.1)
- All database queries use PDO **prepared statements** to prevent SQL injection
- Output is escaped with `htmlspecialchars()` to prevent XSS
- Sessions are used for both user and admin authentication, kept separate (`$_SESSION['user_id']` vs `$_SESSION['admin_id']`)
- Booking references are generated uniquely (e.g. `TP-8F3K2A`) per FR-5

## 7. What's Next / Possible Enhancements
(Matches SRS Section 7 — Future Enhancements)
- Payment gateway integration
- Google Maps embedding for destinations
- Weather API integration
- AI-based recommendation engine
- Native mobile app
