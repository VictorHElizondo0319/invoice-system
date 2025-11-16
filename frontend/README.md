# Invoice System - Frontend

React-based dashboard for managing invoices and payments.

## Features

- **Dashboard**: Overview with KPIs and summary statistics
- **Invoice Management**: Create, view, filter, and manage invoices
- **Payment Processing**: Submit payments and view payment history
- **Responsive Design**: Beautiful UI with Tailwind CSS
- **Real-time Updates**: React Query for efficient data fetching

## Tech Stack

- React 18
- Vite (Build tool)
- React Router (Routing)
- TanStack React Query (Data fetching)
- Axios (HTTP client)
- Tailwind CSS (Styling)

## Prerequisites

- Node.js 18+ and npm/yarn
- Backend API running (see backend README)

## Installation

### 1. Install Dependencies

```bash
npm install
# or
yarn install
```

### 2. Environment Setup

Create a `.env` file in the frontend directory:

```
VITE_API_URL=http://localhost:8000/api
```

## Development

Start the development server:

```bash
npm run dev
# or
yarn dev
```

The application will be available at `http://localhost:3000`

## Building for Production

```bash
npm run build
# or
yarn build
```

The production-ready files will be in the `dist/` directory.

## Project Structure

```
frontend/
├── src/
│   ├── api/          # API client and functions
│   ├── components/   # Reusable components
│   ├── pages/        # Page components
│   ├── App.jsx       # Main app component
│   └── main.jsx      # Entry point
├── index.html
├── package.json
├── vite.config.js
└── tailwind.config.js
```

## Pages

### Dashboard (`/`)
- Display total invoices, paid/unpaid counts
- Show total revenue and outstanding amounts
- Revenue breakdown by status
- Quick action buttons

### Invoice List (`/invoices`)
- Table view of all invoices
- Filter by status and customer name
- View invoice details
- Create new invoice button

### Invoice Detail (`/invoices/:id`)
- View complete invoice information
- Line items with quantities and prices
- Submit invoice (DRAFT → SUBMITTED)
- Process payments
- Payment history
- Delete invoice option

### Create Invoice (`/invoices/create`)
- Form with validation
- Dynamic line items (add/remove)
- Real-time total calculation
- Customer name and due date fields

## API Integration

The frontend communicates with the Laravel backend API through axios. All API calls are defined in `src/api/api.js`:

- `getInvoices(filters)` - Fetch invoices with optional filters
- `getInvoice(id)` - Fetch single invoice details
- `createInvoice(data)` - Create new invoice
- `updateInvoice(id, data)` - Update existing invoice
- `deleteInvoice(id)` - Delete invoice
- `submitInvoice(id)` - Submit invoice
- `createPayment(data)` - Process payment
- `getSummary()` - Get dashboard summary
- `getAnalytics()` - Get analytics data

## Features in Detail

### State Management
Uses React Query for server state management with automatic caching, refetching, and optimistic updates.

### Form Validation
Client-side validation for all forms with helpful error messages.

### Responsive Design
Fully responsive layout that works on mobile, tablet, and desktop devices.

### User Feedback
Loading states, error handling, and success messages for all operations.

## Docker Support

See the main project README for Docker deployment instructions.


