# Project Name

This project is a subscription management system integrated with Stripe for handling user subscriptions and payments. It is built with Laravel and uses Stripe's API for managing subscriptions and processing payments.

## Features

- User registration and authentication
- Subscription plans management
- Stripe integration for handling subscriptions and payments
- Webhooks for handling subscription events
- Checkout flow for user subscription
- Profile page to view and manage user subscriptions

## Prerequisites

Before running this project, make sure you have the following installed on your system:

- PHP (version ^7.3.X)
- Composer (version ^2.X.X)
- Node.js (version ^16.X.X)
- NPM (version ^8.X.X)
- MySQL or any other compatible database server

## Installation

1. Clone the repository:

    git clone https://github.com/jiajing21387177/laravel-subscription-app.git


2. Navigate to the project directory:

    cd laravel-subscription-app

3. Install PHP dependencies:

    composer install

4. Install JavaScript dependencies:

    npm install

5. Create a copy of the `.env.example` file and rename it to `.env`. Update the necessary configuration values such as database credentials and Stripe API keys.

6. Generate the application key:

    php artisan key:generate

7. Run the database migrations:

    php artisan migrate --seed

8. Build the front-end assets:

    npm run dev

9. Start the development server:

    php artisan serve

10. Setup the Stripe CLI and listen to Stripe events:

    https://stripe.com/docs/stripe-cli


11. Access the application in your browser at `http://127.0.0.1:8000`.

## Configuration

The project's main configuration file is located at `.env`. Update this file with your specific environment settings, such as database credentials and Stripe API keys.

## Usage

- Register a user account or log in with an existing account.
- Navigate to the profile page to view and manage your subscriptions.
- Use the provided Stripe integration to subscribe to a plan and complete the payment process.
- Check the webhooks endpoint to handle subscription events from Stripe.

## Contributing

Contributions are welcome! If you find any issues or have suggestions for improvements, please open an issue or submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).
