# Open Source Uber Clone

## Overview
This project is an open-source implementation of a ride-sharing application similar to Uber, built with Laravel and MySQL. It aims to provide a complete, customizable web solution that developers can use as a foundation for building their own ride-sharing platforms.

## Features
- User authentication and profiles
- Driver and rider interfaces
- Real-time location tracking
- Trip request and matching system
- In-app messaging between drivers and riders
- Payment integration
- Rating and review system
- Admin dashboard for monitoring and management

## Technology Stack
- **Backend**: Laravel PHP framework
- **Database**: MySQL
- **Frontend**: Bootstrap, HTML, CSS,Javascript
- **Maps and Location**: Google Maps API
- **Authentication**: Laravel built-in authentication
- **Payment Processing**: Stripe API

## Project Structure
```
├── app/                  # Application code
│   ├── Http/             # Controllers, Middleware, Requests
│   ├── Models/           # Database models
│   ├── Services/         # Business logic
│   ├── Events/           # Event classes
│   └── Listeners/        # Event listeners
├── config/               # Configuration files
├── database/             # Migrations and seeders
│   ├── migrations/       # Database structure
│   └── seeders/          # Initial data
├── public/               # Publicly accessible files
│   ├── css/              # CSS files
│   ├── js/               # JavaScript files
│   └── images/           # Image files
├── resources/            # Views, assets, language files
│   ├── css/              # CSS source files
│   └── views/            # Blade templates
├── routes/               # Application routes
├── storage/              # Application storage
└── tests/                # Test files
```

## Prerequisites
- PHP 8.0 or higher
- Composer
- MySQL 5.7 or higher
- Google Maps API key
- Stripe account for payment processing

## Installation

### Setup
1. Clone the repository:
   ```
   git clone https://github.com/venkatnplus/opensource-uberclone.git
   cd opensource-uberclone
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Create a copy of the `.env.example` file:
   ```
   cp .env.example .env
   ```

4. Generate an application key:
   ```
   php artisan key:generate
   ```

5. Configure your `.env` file with your MySQL database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=uberclone
   DB_USERNAME=your_mysql_username
   DB_PASSWORD=your_mysql_password
   ```

6. Add your Google Maps API key and other necessary configurations to the `.env` file:
   ```
   GOOGLE_MAPS_API_KEY=your_google_maps_api_key
   STRIPE_SECRET_KEY=your_stripe_secret_key
   ```

7. Run the database migrations and seeders:
   ```
   php artisan migrate --seed
   ```

8. Start the Laravel development server:
   ```
   php artisan serve
   ```

## Usage
- The web interface provides both rider and driver functionalities
- Riders can register, book rides, track trips, and make payments
- Drivers can register, accept ride requests, navigate to destinations, and receive payments
- The admin dashboard allows management of users, trips, and system settings

## User Roles
1. **Admin**: Full access to the system, can manage all users, trips, and settings
2. **Driver**: Can accept and complete trips, update their profile, and manage earnings
3. **Rider**: Can request rides, track trips, make payments, and rate drivers

## Frontend Implementation
The frontend is built using:
- **Bootstrap**: For responsive design and UI components
- **HTML**: For structure and content
- **CSS**: For styling and customization
- **Minimal JavaScript**: For interactive elements and map integration

## Database Schema
The database includes tables for:
- Users (with driver/rider types)
- Trips
- Locations
- Payments
- Ratings
- Messages
- Settings

## Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License
This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements
- Uber for inspiration
- Laravel community
- Bootstrap contributors
- All project contributors and supporters

## Maintainers
- [vikramNplus](https://github.com/vikramNplus)

## Last Updated
2025-06-25 10:08:20 UTC

## Roadmap
- [ ] Implement multi-language support
- [ ] Add advanced matching algorithms
- [ ] Develop a fare calculation system based on traffic conditions
- [ ] Integrate with additional payment gateways
- [ ] Create a driver verification system
- [ ] Implement real-time analytics dashboard
- [ ] Enhance mobile responsiveness
