# OrangeApps School Finance ERP

A comprehensive school finance management system built for Philippine educational institutions. Handles the complete financial workflow from budgeting and disbursements to accounts payable/receivable, general ledger, tax compliance (BIR), and financial reporting.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.3)
- **Frontend:** Vue 3, Blade Templates, Alpine.js
- **Styling:** Tailwind CSS
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis
- **Infrastructure:** Docker, Docker Compose
- **PDF/Excel:** DomPDF, Maatwebsite Excel
- **Auth:** Laravel Sanctum

## Features

### Dashboard
- Financial overview with KPIs
- Budget utilization charts
- Cash flow summary
- Pending approvals widget

### Budget Management
- Annual budget creation per department/cost center
- Budget allocation and tracking
- Committed vs. actual monitoring
- Utilization percentage reports

### Disbursement Module
- Disbursement request workflow (Draft > Pending > Approved > Released)
- Multi-level approval chain
- Check and bank transfer payments
- Disbursement voucher printing

### Accounts Payable (AP)
- Vendor master data
- Bill entry with VAT and withholding tax computation
- Debit memos
- Payment processing and allocation
- AP aging reports

### Accounts Receivable (AR)
- Customer/student master data
- Invoice generation
- Collection receipts
- Credit memos
- AR aging reports

### General Ledger (GL)
- Chart of Accounts management
- Journal entry posting
- Recurring journal templates
- Trial balance
- Income statement
- Balance sheet

### Tax & BIR Compliance
- Withholding tax computation (EWT, VAT)
- BIR Form 2307 generation and printing
- Quarterly Alphalist of Payees (QAP)
- Tax code management
- ATC (Alpha Tax Code) lookup

### System Administration
- User management with role-based access
- Multi-campus support
- Accounting period management
- Numbering sequence configuration
- Audit trail logging
- System settings

### Reports & Exports
- All reports exportable to Excel
- Print-optimized views (receipts, checks, BIR forms)
- PDF generation

## Quick Start with Docker

```bash
git clone https://github.com/oaadmin/school-finance-laravel.git
cd school-finance-laravel
cp .env.example .env
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app npm install && npm run build
# Visit http://localhost:8000
# Login: admin@orangeapps.edu.ph / password
# phpMyAdmin: http://localhost:8080
```

## Quick Start without Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure .env with your MySQL credentials
php artisan migrate:fresh --seed
npm install && npm run build
php artisan serve
```

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@orangeapps.edu.ph | password |

## API Documentation

API documentation is available at `/api-docs` when the application is running.

## Module Summary

| Module | Pages |
|--------|-------|
| Dashboard | 1 |
| Budget Management | 4 |
| Disbursements | 5 |
| Accounts Payable | 6 |
| Accounts Receivable | 6 |
| General Ledger | 5 |
| Reports | 8 |
| Tax / BIR | 4 |
| System Admin | 6 |
| **Total** | **45** |

## Database Schema

The system uses 38 migration files covering the following core tables:

- `users` - System users with roles and permissions
- `campuses` - Multi-campus support
- `departments` - Organizational departments
- `cost_centers` - Cost center tracking
- `fund_sources` - Fund source management
- `chart_of_accounts` - GL account structure
- `journal_entries` / `journal_entry_lines` - Double-entry bookkeeping
- `budgets` / `budget_allocations` - Budget tracking
- `disbursement_requests` / `disbursement_items` / `disbursement_payments` - Disbursement workflow
- `vendors` / `ap_bills` / `ap_payments` - Accounts payable
- `customers` / `ar_invoices` / `ar_collections` - Accounts receivable
- `tax_codes` - BIR tax code reference
- `audit_logs` - Complete audit trail
- `settings` - System configuration
- `numbering_sequences` - Auto-numbering for documents

## BIR Compliance Features

- Automatic withholding tax computation based on vendor type
- BIR Form 2307 (Certificate of Creditable Tax Withheld at Source)
- Quarterly Alphalist of Payees (QAP) export in BIR format
- ATC (Alphanumeric Tax Code) reference table
- VAT and non-VAT vendor handling
- Expanded withholding tax (EWT) support

## License

MIT License. See [LICENSE](LICENSE) for details.
