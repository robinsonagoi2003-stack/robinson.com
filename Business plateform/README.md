# Business Management Platform

A PHP and MySQL business management platform with user registration, secure authentication, password reset, dashboards, task management, and AI insights.

## Features
- User registration with validation
- Login with session-based authentication
- Password reset via email token system
- Secure password hashing with bcrypt
- Dashboard metrics for revenue, orders, customers, and growth
- Recent activity feed and task management system
- Quick action buttons for easier workflow
- AI business insights and task automation
- Local AI fallback when OpenAI API key isn't set
- Optional OpenAI support for enhanced recommendations
- Dedicated task CRUD page
- Charts and graphs on the dashboard
- SMTP email support for password reset

## Technical Stack
- PHP backend with MySQL
- Responsive HTML/CSS
- AJAX for AI interactions
- Session-based authentication

## Quick Start
1. Place the project in your web server folder (XAMPP/WAMP/MAMP).
2. Update database credentials in `config.php` if needed:

```php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'business_management';
```

3. Run the database setup:

`http://localhost/Business%20plateform/init_db.php`

4. Access the site:

`http://localhost/Business%20plateform/index.php`

5. Register, login, and use the dashboard.

## Default Admin
Create the first account using the registration page. That account will act as your initial admin user.

## AI Setup (Optional)
To unlock advanced OpenAI responses:

1. Get an API key from OpenAI.
2. Add it into `config.php`:

```php
$openai_api_key = 'sk-your-key-here';
```

Without a key, the system will still return smart local responses.

## Email Setup for Password Reset
Password reset links are created automatically. To send email:

- Configure email in `config.php` by setting `$smtp_enabled = true`, and updating `$smtp_host`, `$smtp_port`, `$smtp_user`, `$smtp_pass`, and `$mail_from`.
- Make sure your SMTP provider credentials are valid and the server allows outbound SMTP connections.

If email is not configured or SMTP cannot connect, the reset page will display a direct token link.

## Customization
- Styling: `assets/css/styles.css`
- JavaScript: `assets/js/app.js`
- Dashboard widgets: `dashboard.php`
- AI prompts: `ai_action.php`

## File Structure
Business plateform/
├── index.php
├── register.php
├── login.php
├── dashboard.php
├── reset_request.php
├── reset_password.php
├── tasks.php
├── ai_action.php
├── init_db.php
├── config.php
├── functions.php
├── header.php
├── footer.php
├── assets/
│   ├── css/styles.css
│   ├── js/app.js
│   └── img/dashboard-illustration.svg
└── database.sql

## Security Features
- Password hashing with bcrypt
- Prepared statements to avoid SQL injection
- Output escaping for XSS protection
- Session-based authentication
- Password reset token expiration (1 hour)

## Next Enhancements
You can extend the platform with:
- Inventory management
- CRM customer profiles
- Invoicing and receipts
- Roles and permissions
- Real-time alerts
- Charts and reports
- Document uploads
- PDF/Excel exports

The platform is now updated with a richer dashboard, task system, activity feed, and email token reset workflow.
