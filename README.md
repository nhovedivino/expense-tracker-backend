# Expense Tracker Backend API

A Laravel-based REST API for managing personal expenses and savings with comprehensive analytics features.

## Features

- **Authentication**: User registration, login, logout using Laravel Sanctum
- **Savings Management**: Full CRUD operations for savings records
- **Expense Management**: Full CRUD operations for expense records with categories
- **Analytics**: Monthly and yearly expense analysis, category breakdowns, savings vs expenses comparison
- **Security**: API token-based authentication with user-specific data access

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd expense-tracker-backend
```

2. Install PHP dependencies
```bash
composer install
```

3. Set up environment file
```bash
cp .env.example .env
php artisan key:generate
```

4. Run database migrations
```bash
php artisan migrate
```

5. Start the development server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## API Documentation

See [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for detailed API endpoints and usage examples.

## Database Schema

### Users
- Standard Laravel user authentication fields
- Relationships to savings and expenses

### Savings
- `user_id` (foreign key)
- `amount` (decimal)
- `description` (string)
- `date` (date)

### Expenses
- `user_id` (foreign key)
- `amount` (decimal)
- `description` (string)
- `category` (string)
- `date` (date)

## Testing

You can test the API using tools like Postman, Insomnia, or curl. Make sure to:

1. Register a new user or login to get an authentication token
2. Include the token in the `Authorization: Bearer {token}` header for protected routes
3. Use appropriate JSON payloads for POST and PUT requests

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
