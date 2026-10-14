# FreshMart - Online Grocery Store

A web application for ordering fresh groceries online. Built for the Web Application Development final project.

**Tech stack:** PHP 8 (PDO), MySQL, HTML5, Tailwind CSS, vanilla JavaScript

## Features
- User registration, login and logout (passwords hashed with `password_hash`, bcrypt)
- Role-based access: **admin** and **customer**
- Customers: browse products by category, add to cart (AJAX), update cart, checkout, view order history
- Admin panel: full CRUD for products and categories, view and update order status
- Client-side validation (JavaScript) and server-side validation (PHP)
- PDO prepared statements (SQL injection protection), output escaping (XSS protection), CSRF tokens
- Responsive design for phone, tablet and desktop

## Database
`database/schema.sql` creates `freshmart_db` with the tables: `users`, `categories`, `products`, `orders`, `order_items`, plus sample categories and products.

## Setup (XAMPP, localhost)
1. Copy this folder to `C:\xampp\htdocs\freshmart`
2. Start **Apache** and **MySQL** in XAMPP
3. Open `http://localhost/phpmyadmin`, click **Import**, and import `database/schema.sql`
4. Open `http://localhost/freshmart/setup_admin.php` once to create the admin account, then delete that file
5. Visit `http://localhost/freshmart/`

## Live demo
https://marangelo-malvarosa.gt.tc

## Test credentials
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@freshmart.com | Admin@123 |
| Customer | Register a new account on the Sign Up page | |

Admin panel: `/admin/`

## Deployment (InfinityFree)
Hosted on InfinityFree. Import `database/schema_hosting.sql` (it has no CREATE DATABASE statement) into the hosting database and set the credentials in `config/database.php`. The credentials in this repository are localhost placeholders; real hosting credentials are not committed.

## Author
Mar Angelo Malvarosa
