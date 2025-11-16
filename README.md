# Mini Invoicing & Payment System

A full-stack web application for managing invoices and payments, built with Laravel (backend), React (frontend), and PostgreSQL (database).

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Architecture](#architecture)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Local Development](#local-development)
- [Docker Deployment](#docker-deployment)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [CI/CD Pipeline](#cicd-pipeline)
- [Design Decisions](#design-decisions)

## 🎯 Overview

This project is a production-ready invoicing and payment system that allows users to:
- Create and manage invoices with line items
- Submit invoices to customers
- Process mock payments
- View analytics and reporting dashboards
- Monitor system health

## ✨ Features

### Backend (Laravel API)
- **Invoice Management**: CRUD operations for invoices with validation
- **Payment Processing**: Mock payment system with transaction tracking
- **Reporting**: Analytics dashboard with KPIs and summaries
- **Health Checks**: Monitoring endpoints for DevOps
- **Database Migrations**: Structured schema with foreign keys
- **Unit & Feature Tests**: Comprehensive test coverage

### Frontend (React)
- **Dashboard**: Real-time KPIs and summary statistics
- **Invoice List**: Filterable table with status and customer search
- **Invoice Detail**: Complete invoice view with payment processing
- **Create Invoice**: Dynamic form with multiple line items
- **Responsive Design**: Mobile-first design with Tailwind CSS

### DevOps
- **Docker Containerization**: Multi-container setup with Docker Compose
- **CI/CD Pipeline**: GitHub Actions for automated testing and building
- **Health Monitoring**: Application and database health checks
- **Environment Management**: Secure configuration with .env files

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│                     User Browser                         │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│            React Frontend (Port 3000)                    │
│  ┌─────────────────────────────────────────────────┐   │
│  │ • Dashboard  • Invoice List                      │   │
│  │ • Invoice Detail  • Create Invoice               │   │
│  │ • React Query (State Management)                 │   │
│  │ • Tailwind CSS (Styling)                         │   │
│  └─────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────┘
                     │ HTTP/REST API
                     ▼
┌─────────────────────────────────────────────────────────┐
│         Laravel Backend API (Port 8000)                  │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Controllers:                                     │   │
│  │ • InvoiceController  • PaymentController         │   │
│  │ • ReportController   • HealthController          │   │
│  │                                                   │   │
│  │ Models: Invoice, InvoiceItem, Payment           │   │
│  │                                                   │   │
│  │ Validation, Transactions, Error Handling         │   │
│  └─────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────┘
                     │ PDO/Eloquent ORM
                     ▼
┌─────────────────────────────────────────────────────────┐
│         PostgreSQL Database (Port 5432)                  │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Tables:                                          │   │
│  │ • invoices      (id, customer_name, status...)   │   │
│  │ • invoice_items (id, invoice_id, desc, qty...)   │   │
│  │ • payments      (id, invoice_id, amount...)      │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 10.x
- **Language**: PHP 8.2+
- **Database**: PostgreSQL 15
- **ORM**: Eloquent
- **Testing**: PHPUnit

### Frontend
- **Framework**: React 18
- **Build Tool**: Vite
- **State Management**: TanStack React Query
- **Routing**: React Router v6
- **HTTP Client**: Axios
- **Styling**: Tailwind CSS

### DevOps
- **Containerization**: Docker & Docker Compose
- **CI/CD**: GitHub Actions
- **Web Server**: Nginx (for frontend), PHP-FPM (for backend)

## 📦 Prerequisites

### For Local Development
- PHP 8.1 or higher
- Composer
- Node.js 18+ and npm
- PostgreSQL 14+

### For Docker Deployment
- Docker 20.10+
- Docker Compose 2.0+

## 🚀 Quick Start

### Using Docker (Recommended)

1. **Clone the repository**
```bash
git clone <repository-url>
cd <repository-name>
```

2. **Start all services**
```bash
docker compose up -d
```

3. **Access the application**
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api
- Health Check: http://localhost:8000/api/health

4. **Stop services**
```bash
docker compose down
```

## 💻 Local Development

### Backend Setup

1. Navigate to backend directory
```bash
cd backend
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
# Edit .env with your database credentials
```

4. Generate application key
```bash
php artisan key:generate
```

5. Run migrations
```bash
php artisan migrate
```

6. Start development server
```bash
php artisan serve
```

Backend will be available at http://localhost:8000

### Frontend Setup

1. Navigate to frontend directory
```bash
cd frontend
```

2. Install dependencies
```bash
npm install
```

3. Configure environment
```bash
cp .env.example .env
# Edit VITE_API_URL if needed
```

4. Start development server
```bash
npm run dev
```

Frontend will be available at http://localhost:3000

## 🐳 Docker Deployment

### Services

The Docker Compose setup includes three services:

1. **Database (PostgreSQL)**
   - Port: 5432
   - Volume: Persistent data storage

2. **Backend (Laravel API)**
   - Port: 8000
   - Auto-runs migrations on startup
   - Health check enabled

3. **Frontend (React + Nginx)**
   - Port: 3000
   - Proxies API requests to backend
   - Health check enabled

### Environment Variables

Create `.env` files in backend and frontend directories based on `.env.example`:

**Backend `.env`:**
```
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=invoice_system
DB_USERNAME=invoiceuser
DB_PASSWORD=invoicepass
```

**Frontend `.env`:**
```
VITE_API_URL=http://localhost:8000/api
```

### Commands

```bash
# Build images
docker compose build

# Start services
docker compose up -d

# View logs
docker compose logs -f

# Stop services
docker compose down

# Remove volumes (reset database)
docker compose down -v
```

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Endpoints

#### Invoices
- `GET /invoices` - List all invoices (supports filters: status, customer_name)
- `POST /invoices` - Create new invoice
- `GET /invoices/{id}` - Get invoice details
- `PUT /invoices/{id}` - Update invoice (DRAFT only)
- `DELETE /invoices/{id}` - Delete invoice (not PAID)
- `PUT /invoices/{id}/submit` - Submit invoice (DRAFT → SUBMITTED)

#### Payments
- `POST /payments` - Process payment
  ```json
  {
    "invoice_id": 1,
    "amount": 1000.00
  }
  ```
- `GET /payments` - List payments (supports filter: invoice_id)

#### Reports
- `GET /reports/summary` - Get KPI summary
- `GET /reports/analytics` - Get detailed analytics

#### Health
- `GET /health` - Application health check

### Example: Create Invoice

```bash
curl -X POST http://localhost:8000/api/invoices \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "due_date": "2024-12-31",
    "items": [
      {
        "description": "Web Development",
        "qty": 10,
        "price": 100.00
      }
    ]
  }'
```

## 🧪 Testing

### Backend Tests

```bash
cd backend

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

Test coverage includes:
- Invoice creation and validation
- Payment processing
- Invoice status transitions
- Report generation
- Health checks

### Frontend Tests

The frontend uses React Query for state management and includes manual testing via the UI.

## 🔄 CI/CD Pipeline

The project includes a GitHub Actions workflow that:

1. **Backend Tests**
   - Sets up PHP 8.2
   - Installs Composer dependencies
   - Runs PHPUnit tests with PostgreSQL

2. **Frontend Build**
   - Sets up Node.js 18
   - Installs npm dependencies
   - Runs linter
   - Builds production bundle

3. **Docker Build**
   - Builds backend and frontend Docker images
   - Tests Docker Compose configuration
   - Caches layers for faster builds

4. **Health Check**
   - Starts all services
   - Validates backend health endpoint
   - Validates frontend accessibility

### Triggering the Pipeline

The pipeline runs on:
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches

## 🎨 Design Decisions

### Backend

1. **Laravel Framework**: Chosen for its robust ecosystem, built-in features (ORM, validation, migrations), and excellent documentation.

2. **RESTful API Design**: Simple, stateless API following REST principles for easy integration and scalability.

3. **Database Transactions**: Used for operations involving multiple tables (invoice + items) to ensure data consistency.

4. **Status-Based Workflow**: Invoice statuses (DRAFT → SUBMITTED → PAID) enforce business logic and prevent invalid operations.

5. **Eloquent ORM**: Provides clean, expressive syntax for database operations with relationships.

### Frontend

1. **React + Vite**: Modern tooling for fast development and optimized production builds.

2. **TanStack React Query**: Simplifies server state management with automatic caching, refetching, and error handling.

3. **Tailwind CSS**: Utility-first CSS framework for rapid UI development with consistent design.

4. **React Router**: Client-side routing for a smooth single-page application experience.

5. **Component Architecture**: Reusable components with clear separation between pages and layout.

### DevOps

1. **Docker Multi-Stage Builds**: Optimized frontend build with separate build and production stages.

2. **Health Checks**: Container-level health checks ensure services are ready before accepting traffic.

3. **Volume Persistence**: PostgreSQL data persists across container restarts.

4. **Environment Variables**: Secure configuration management without committing secrets.

5. **GitHub Actions**: Automated testing and building on every push/PR to catch issues early.

### Database Schema

1. **Foreign Key Constraints**: Enforces referential integrity between invoices, items, and payments.

2. **Cascade Deletes**: Automatically removes related items and payments when invoice is deleted.

3. **Decimal Types**: Used for monetary values to avoid floating-point precision issues.

4. **Timestamps**: Automatic tracking of creation and update times for audit purposes.

## 📁 Project Structure

```
.
├── backend/                  # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/  # API controllers
│   │   └── Models/           # Eloquent models
│   ├── database/
│   │   ├── migrations/       # Database migrations
│   │   └── factories/        # Test data factories
│   ├── routes/
│   │   └── api.php           # API routes
│   ├── tests/                # PHPUnit tests
│   ├── Dockerfile
│   └── README.md
│
├── frontend/                 # React application
│   ├── src/
│   │   ├── api/              # API client
│   │   ├── components/       # Reusable components
│   │   ├── pages/            # Page components
│   │   ├── App.jsx
│   │   └── main.jsx
│   ├── Dockerfile
│   ├── nginx.conf
│   └── README.md
│
├── .github/
│   └── workflows/
│       └── ci-cd.yml         # GitHub Actions workflow
│
├── docker-compose.yml        # Multi-container setup
└── README.md                 # This file
```

## 🔒 Security Considerations

1. **Environment Variables**: Sensitive data stored in .env files (not committed to git)
2. **Input Validation**: All API inputs validated using Laravel's validation rules
3. **SQL Injection Prevention**: Eloquent ORM uses parameterized queries
4. **CORS Configuration**: Configured to allow frontend-backend communication
5. **Database Transactions**: Prevents partial data commits on errors

## 🚦 Monitoring & Health Checks

### Health Check Endpoint

`GET /api/health`

Returns:
```json
{
  "status": "healthy",
  "timestamp": "2024-01-15T10:30:00Z",
  "service": "invoice-api",
  "checks": {
    "database": {
      "status": "up",
      "message": "Database connection successful"
    },
    "application": {
      "status": "up",
      "php_version": "8.2.0",
      "laravel_version": "10.x"
    }
  }
}
```

### Container Health Checks

Docker containers include health checks:
- **Backend**: Curls the `/api/health` endpoint every 30s
- **Frontend**: Checks Nginx response every 30s
- **Database**: Validates PostgreSQL readiness every 10s

## 📈 Future Enhancements (Not Implemented)

The following bonus features were identified but not implemented in the current version:

1. **Authentication (JWT)**: User login/registration system
2. **PDF Export**: Generate PDF invoices
3. **Role-Based Access**: Admin vs User permissions
4. **File Attachments**: S3-like storage for invoice attachments
5. **Webhooks**: Notifications when invoice is paid

## 📄 License

This project is created as a take-home assignment.

## 👥 Author

Created as a full-stack development assessment.

---

## 📞 Support

For questions or issues, please refer to the individual README files in the backend/ and frontend/ directories for more detailed documentation.


