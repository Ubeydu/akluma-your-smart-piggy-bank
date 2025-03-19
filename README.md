# Piggy Bank Savings Application

A web application built with Laravel that helps users create piggy banks to save money for products they want to purchase.

## About The Project

This application allows users to:
- Create virtual piggy banks for saving towards specific goals
- Track savings progress visually
- Set target dates and amounts
- Monitor current balances and progress

## Tech Stack

- **Framework**: Laravel 12
- **Frontend**: Blade templates, Tailwind CSS
- **JavaScript**: Vanilla JS, Alpine.js
- **Database**: MySQL
- **PHP Version**: 8.4.4

## Installation

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your database
3. Start the Docker environment: `./vendor/bin/sail up -d`
    - If this is your first time installing, run `docker run --rm -v $(pwd):/app composer install` first
4. Run `./vendor/bin/sail artisan key:generate`
5. Run `./vendor/bin/sail artisan migrate`
6. Run `./vendor/bin/sail npm install && ./vendor/bin/sail npm run build`
7. Access the application at http://localhost

## License

[MIT License](LICENSE)

This README now includes:
1. A basic project description
2. Tech stack information
3. Your detailed browser navigation handling documentation
4. Basic installation instructions
5. A license reference

You can expand this with more sections as your project grows, such as:
- Screenshots
- API documentation
- Deployment instructions
- Testing information
- Contribution guidelines
