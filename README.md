# Restaurant Booking System

A PHP-based restaurant booking system with customer booking form and admin dashboard.

## Features
- Customer booking form with personal info and booking details
- Booking confirmation with edit option
- Email notifications for bookings
- Admin dashboard to manage bookings
- Add/remove admin users
- Send emails to customers

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for PHPMailer)

## Installation
1. Clone the repository
2. Copy `.env.example` to `.env` and update configuration
3. Import SQL files from `/sql` directory
4. Run `composer install` to install dependencies
5. Configure your web server to point to the project root

## Security
- Never commit `.env` file
- Change default admin credentials
- Enable HTTPS in production
- Keep PHP and dependencies updated

## License
MIT

## Deployment / Production
On the server,run:

composer install --no-dev --optimize-autoloader

## Check outdated packages first:
composer outdated