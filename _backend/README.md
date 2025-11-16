# Mini Invoicing & Payment System - Backend API

Laravel-based REST API for managing invoices and payments.

## Features

- **Invoice Management**: Create, view, update, submit, and delete invoices
- **Payment Processing**: Process mock payments with automatic invoice status updates
- **Reporting**: Get analytics and KPIs (total invoices, paid/unpaid, outstanding amounts)
- **Health Checks**: Monitor application and database health
- **Comprehensive Tests**: Unit and feature tests with PHPUnit

## Prerequisites

- PHP 8.1 or higher
- Composer
- PostgreSQL 14+ (or SQLite for local development)
- Laravel 10.x

## Installation

### 1. Install Dependencies

```bash
composer install
```

### 2. Environment Setup

```bash
cp .env.example .env
```

Edit `.env` file with your database credentials:

```
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=invoice_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Invoices

- `GET /api/invoices` - List all invoices (supports filtering by status, customer_name)
- `POST /api/invoices` - Create a new invoice
- `GET /api/invoices/{id}` - Get invoice details
- `PUT /api/invoices/{id}` - Update invoice (only DRAFT invoices)
- `DELETE /api/invoices/{id}` - Delete invoice (except PAID invoices)
- `PUT /api/invoices/{id}/submit` - Submit invoice (changes status from DRAFT to SUBMITTED)

### Payments

- `POST /api/payments` - Process a payment
- `GET /api/payments` - List all payments (supports filtering by invoice_id)

### Reports

- `GET /api/reports/summary` - Get summary KPIs
- `GET /api/reports/analytics` - Get detailed analytics

### Health

- `GET /api/health` - Health check endpoint

## API Examples

### Create Invoice

```bash
POST /api/invoices
Content-Type: application/json

{
  "customer_name": "John Doe",
  "due_date": "2024-12-31",
  "items": [
    {
      "description": "Web Development",
      "qty": 10,
      "price": 100.00
    },
    {
      "description": "Design Services",
      "qty": 5,
      "price": 50.00
    }
  ]
}
```

### Process Payment

```bash
POST /api/payments
Content-Type: application/json

{
  "invoice_id": 1,
  "amount": 1250.00
}
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## Database Schema

### Invoices Table
- `id` - Primary key
- `customer_name` - Customer name
- `due_date` - Payment due date
- `status` - Invoice status (DRAFT, SUBMITTED, PAID, OVERDUE)
- `total_amount` - Total invoice amount
- `paid_amount` - Amount paid so far
- `created_at`, `updated_at` - Timestamps

### Invoice Items Table
- `id` - Primary key
- `invoice_id` - Foreign key to invoices
- `description` - Item description
- `qty` - Quantity
- `price` - Unit price
- `created_at`, `updated_at` - Timestamps

### Payments Table
- `id` - Primary key
- `invoice_id` - Foreign key to invoices
- `amount` - Payment amount
- `payment_method` - Payment method (mock)
- `transaction_id` - Unique transaction identifier
- `status` - Payment status (SUCCESS, FAILED, PENDING)
- `created_at`, `updated_at` - Timestamps

## Architecture

The backend follows Laravel best practices:

- **Models**: Eloquent ORM models for Invoice, InvoiceItem, and Payment
- **Controllers**: RESTful controllers for handling API requests
- **Migrations**: Database schema version control
- **Factories**: Test data factories for automated testing
- **Tests**: Comprehensive feature tests for all endpoints
- **Validation**: Request validation for all input data
- **Transactions**: Database transactions for data integrity

## Logging

Application logs are stored in `storage/logs/laravel.log`. Health check failures and errors are automatically logged in JSON format.

## Docker Support

See the main project README for Docker deployment instructions.


