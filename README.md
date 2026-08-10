# Service Center Management System

A production-grade, full-featured web application for managing the daily operations of a mobile & electronics repair service center. Built with **Laravel 13**, **PHP 8.3**, **Blade**, **Alpine.js**, and **Tailwind CSS**, and containerized with **Docker Compose**, the system covers the entire customer repair lifecycle — intake, diagnosis, technician assignment, parts consumption, billing, payments, and warranty tracking — alongside inventory management, procurement, reporting, and a token-authenticated REST API.

---

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Getting Started](#getting-started)
- [Configuration](#configuration)
- [Demo Data & Credentials](#demo-data--credentials)
- [Application Modules](#application-modules)
- [REST API](#rest-api)
- [Background Jobs & Notifications](#background-jobs--notifications)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Performance & Security](#performance--security)
- [Roadmap](#roadmap)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

Service centers juggle many moving parts: repair requests, customer devices, technician workloads, spare-part inventory, invoices, and post-repair warranties. **Service Center Management** consolidates these into a single, cohesive application so that operators can move a repair from the counter to the workbench to the point of sale — and keep track of what happens after the customer walks out the door.

The application is intentionally service-driven: business rules (status transitions, stock movement, invoice math, warranty expiration) live in dedicated service classes rather than controllers, keeping the domain logic testable and the HTTP layer thin. Every core flow — work order state changes, parts usage, purchase order receiving, invoice generation, and payment recording — is covered by an automated test suite.

---

## Key Features

### Work Order Management
- Create work orders against any customer device with a full problem description, priority, and estimated cost.
- Assign technicians with an auditable assignment trail.
- **Strict, validated status workflow**: `new → diagnosed → approved → ready → in_repair → completed` (or `cancelled` at the intake/diagnosis stages). Invalid transitions are rejected by the service layer.
- Automatic `completed_at` stamping when a work order is completed.
- Full **status history** per work order, with the actor and notes recorded on every change.
- Free-form notes, part usage, and service (repair) type association per work order.

### Customer & Device Management
- Customer profiles with contact details, NID, city, contact preference, and loyalty flag.
- Register multiple devices per customer (type, brand, model, serial number, color).
- Drill into per-customer work order history.

### Inventory & Procurement
- Parts catalog organized by category, with supplier linkage, cost/selling price, quantity on hand, and minimum-stock thresholds.
- **Low-stock detection** with an alert event and a dedicated low-stock report.
- Manual restocking and stock adjustments, each recorded as a **stock movement** for a full audit trail.
- **Purchase orders** with line items, supplier references, and a `pending → approved → received → cancelled` lifecycle; receiving stock automatically increments inventory.

### Billing & Payments
- Invoice generation with service-charge override, parts cost, discount, and configurable tax rate.
- Sequential, human-friendly numbering: `INV-2026-00001`.
- Payment recording via cash, card, bank transfer, or mobile banking (partial or full).
- Automatic invoice status transitions (`unpaid → partial → paid`) and **refund** processing that reverts amounts and status.
- Printable PDF invoices (DOMPDF) and spreadsheet report export (Maatwebsite/Excel).

### Warranty Management
- Register warranties against completed work orders, optionally tied to a specific part, with a configurable default duration (30 days by default).
- Automatic `end_date` calculation, remaining-days countdown, and expiry status.
- **Revoke** support for warranties voided by damage or policy.
- Warranties index with status badges (`active`, `expired`, `revoked`).

### Reporting & Export
- Revenue report with date-range filtering (gross, discounts, tax, net).
- Technician performance analytics (completed orders, revenue, average turnaround).
- Popular repair services ranking and inventory valuation report.
- CSV/XLSX export of report data and printable invoice PDFs.

### Settings
- Shop profile (name, address, phone, email, currency symbol, invoice footer).
- Tax rate and default warranty duration.
- Device types, brands, part categories, and repair services (with per-service estimated cost and turnaround time).

### REST API
- Full JSON API secured with **Laravel Sanctum** personal access tokens.
- Resourceful endpoints for customers, devices, work orders, technicians, parts, invoices, payments, and reports.

---

## Technology Stack

| Layer             | Technology                                        |
|-------------------|---------------------------------------------------|
| Backend           | PHP 8.3, Laravel 13.8                             |
| Frontend          | Blade, Alpine.js, Tailwind CSS 4                  |
| Database          | MySQL 8.0 (primary), SQLite (test suite)          |
| Cache / Queue     | Database driver by default; Redis 7 in the stack   |
| API Authentication| Laravel Sanctum (bearer tokens)                  |
| Authorization     | spatie/laravel-permission (roles: `admin`, `staff`) |
| PDF Generation    | barryvdh/laravel-dompdf                           |
| Spreadsheets      | maatwebsite/excel                                 |
| Testing           | PHPUnit 12, Faker, Mockery                        |
| Tooling           | Laravel Pint, Laravel Pail, Laravel Tinker        |
| Infrastructure    | Docker Compose (Nginx + PHP-FPM, MySQL, Redis, phpMyAdmin) |

---

## Architecture

The application follows Laravel's standard structure with a deliberate separation of concerns:

```
HTTP layer (controllers, form requests, validation)
        │
        ▼
Service layer (WorkOrderService, PartService, BillingService, ReportService, NotificationService)
        │
        ▼
Domain layer (Eloquent models, enums, relationships)
        │
        ▼
Persistence (migrations, MySQL / SQLite)
```

**Service layer.** Controllers are thin. Complex, transactional business logic lives in:

| Service                | Responsibility                                                            |
|------------------------|---------------------------------------------------------------------------|
| `WorkOrderService`     | Creating work orders, assigning technicians, enforcing status transitions, recording history |
| `PartService`          | Parts usage (stock deduction + movement), restocking, stock adjustments, low-stock detection |
| `BillingService`       | Invoice breakdowns (service charge, parts, discount, tax), invoice generation, payments, refunds, invoice numbering |
| `ReportService`        | Revenue, technician performance, popular repairs, and inventory analytics |
| `NotificationService`  | Customer-facing status/completion/low-stock messages (logger-backed, SMS/email hook points) |

**Enums** give the domain strong typing: `WorkOrderStatus`, `WorkOrderPriority`, `InvoiceStatus`, `PaymentMethod`, `PurchaseOrderStatus`, and `StockMovementType`.

**Queued work.** Time-independent side effects are dispatched to the queue as `ShouldQueue` jobs — `SendStatusUpdate`, `SendCompletionAlert`, `GenerateInvoice`, and `CheckLowStock` — keeping HTTP responses fast and behavior consistent. The queue uses the database driver by default (switchable to Redis via `QUEUE_CONNECTION`).

**Events.** `LowStockAlert` is fired whenever a part drops to or below its minimum threshold, decoupling the detection from any downstream handling.

---

## Getting Started

### Prerequisites

- Docker Engine 24+ and Docker Compose v2
- Git
- 4 GB of free RAM (recommended)

The application is fully containerized; **PHP, Composer, Node.js, and MySQL are not required on the host**. All dependencies are installed during the Docker image build and live in named volumes.

### Installation

```bash
# 1. Clone the repository
git clone <repository-url> service-center-management
cd service-center-management

# 2. Create the environment file
cp .env.example .env

# 3. Build and start the stack (app, MySQL, Redis, phpMyAdmin)
docker compose up -d --build

# 4. Generate the application key
docker compose exec app php artisan key:generate

# 5. Run migrations and seed demo data
docker compose exec app php artisan migrate --seed
```

The application is now available at:

| Service     | URL                       | Credentials                                     |
|-------------|---------------------------|-------------------------------------------------|
| Web app     | http://localhost:8086     | `admin@example.com` / `password`                |
| phpMyAdmin  | http://localhost:8087     | `service_center` / `service-center-secret`      |
| MySQL       | `localhost:3309`          | `service_center` / `service-center-secret`      |
| Redis       | `localhost:6381`          | (no auth)                                       |

> **Note:** `vendor/`, `node_modules/`, and `storage/` are Docker named volumes. You never run `composer install` or `npm install` on the host — changes are picked up via the bind-mounted source directory, and new dependencies require a rebuild (`docker compose up -d --build`).

### Useful Commands

```bash
# Follow the app / queue logs
docker compose logs -f app

# Run the test suite inside the container
docker compose exec app php artisan test

# Open an interactive console (Tinker)
docker compose exec app php artisan tinker

# Tail application logs with Laravel Pail
docker compose exec app php artisan pail
```

---

## Configuration

Configuration is read from `.env` (mirrored into the app container via `env_file`). Key variables:

| Variable          | Default                 | Purpose                                   |
|-------------------|-------------------------|-------------------------------------------|
| `APP_ENV`         | `local`                 | Application environment                   |
| `APP_URL`         | `http://localhost:8086` | Base URL used for links and assets        |
| `DB_CONNECTION`   | `mysql`                 | Database driver                           |
| `DB_HOST`         | `db`                    | Database host (service name in Docker)    |
| `DB_DATABASE`     | `service_center`        | Database name                             |
| `DB_USERNAME`     | `service_center`        | Database user                             |
| `DB_PASSWORD`     | `service-center-secret` | Database password                         |
| `REDIS_HOST`      | `cache`                 | Redis host (cache/queue driver option)          |
| `QUEUE_CONNECTION`| `database`              | Queue driver for background jobs                |

Business-level settings (shop name, address, phone, currency symbol, tax rate, invoice footer, default warranty days) are stored in the `settings` table and editable at runtime via the **Settings** screen — no code changes required.

---

## Demo Data & Credentials

The database seeder provisions everything you need to explore the application:

- An **admin** user with the `admin` role and a `staff` role.
- A technician roster, suppliers, part catalog (with categories), and device catalog (brands, device types).
- Repair service catalog with estimated costs and turnaround times.
- A sample customer with a registered device.
- Default settings (tax rate **5%**, **30-day** default warranty, shop profile).

| Role  | Email              | Password   |
|-------|--------------------|------------|
| Admin | `admin@example.com` | `password` |

Seed data is idempotent (`updateOrCreate`) and safe to re-run.

---

## Application Modules

### Dashboard
A live operational overview: active work orders, revenue collected, unpaid invoices, pending/low-stock inventory, warranty status, recent work orders, and a work-order-by-status breakdown.

### Work Orders
- **List / search / filter** by status, priority, technician, and date range.
- **Create** with customer, device, service type, priority, estimated cost, and estimated completion date.
- **Detail view** showing device info, technician, full status history, notes, parts used, invoice, and warranty.
- **Actions**: assign technician, advance status along the validated workflow, add notes, attach parts, generate invoice, and register a warranty.

### Customers & Devices
Manage the customer base and the devices they bring in. Every device is linked to its owner and its repair history is one click away.

### Inventory & Purchase Orders
Track part stock with low-stock warnings, restock against purchase orders, and review the complete stock-movement ledger (`in`, `out`, `adjustment`).

### Billing
Generate invoices from work orders, override the service charge, apply discounts and tax, record partial or full payments, and issue refunds. Each invoice is printable as a PDF.

### Warranties
Browse all warranties with remaining-days countdowns, register new warranties on completed work orders, and revoke voided coverage. Status badges distinguish `active`, `expired`, and `revoked` coverage.

### Reports
Revenue analytics, technician performance, popular repairs, and inventory valuation — with date-range filtering and CSV/XLSX export.

### Settings
Configure the shop profile, tax rate, default warranty duration, and reference data (device types, brands, part categories, repair services).

---

## REST API

The API is versioned under the `/api` prefix and protected by **Laravel Sanctum** personal access tokens. Except for `POST /api/login`, every endpoint requires an `Authorization: Bearer <token>` header.

### Authentication

```bash
# Obtain a token
curl -X POST http://localhost:8086/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Response
# { "message": "Login successful.", "token": "...", "user": { ... } }
```

### Endpoint Reference

#### Auth
| Method | Path          | Description              |
|--------|---------------|--------------------------|
| POST   | `/api/login`  | Authenticate, return token |
| POST   | `/api/logout` | Revoke current token     |
| GET    | `/api/user`   | Current authenticated user |

#### Customers
| Method | Path                                  | Description                       |
|--------|---------------------------------------|-----------------------------------|
| GET    | `/api/customers`                      | List customers                    |
| POST   | `/api/customers`                      | Create a customer                 |
| GET    | `/api/customers/{customer}`           | Show a customer                   |
| PUT    | `/api/customers/{customer}`           | Update a customer                 |
| GET    | `/api/customers/{customer}/devices`   | List a customer's devices         |
| POST   | `/api/customers/{customer}/devices`   | Register a device for a customer  |
| GET    | `/api/customers/{customer}/work-orders`| Customer work-order history       |

#### Work Orders
| Method | Path                                        | Description                              |
|--------|---------------------------------------------|------------------------------------------|
| GET    | `/api/work-orders`                          | List work orders                         |
| POST   | `/api/work-orders`                          | Create a work order                      |
| GET    | `/api/work-orders/{workOrder}`              | Show a work order                        |
| PUT    | `/api/work-orders/{workOrder}`              | Update a work order                      |
| POST   | `/api/work-orders/{workOrder}/assign`       | Assign a technician                      |
| POST   | `/api/work-orders/{workOrder}/status`       | Transition status                        |
| GET    | `/api/work-orders/{workOrder}/history`      | Status history                           |
| GET    | `/api/work-orders/{workOrder}/notes`        | List notes                               |
| POST   | `/api/work-orders/{workOrder}/notes`        | Add a note                               |
| POST   | `/api/work-orders/{workOrder}/parts`        | Use parts against the work order         |

#### Technicians
| Method | Path                                          | Description                          |
|--------|-----------------------------------------------|--------------------------------------|
| GET    | `/api/technicians`                            | List technicians                     |
| POST   | `/api/technicians`                            | Create a technician                  |
| GET    | `/api/technicians/{technician}`               | Show a technician                    |
| PUT    | `/api/technicians/{technician}`               | Update a technician                  |
| GET    | `/api/technicians/{technician}/work-orders`   | Technician's work orders             |
| GET    | `/api/technicians/{technician}/performance`   | Technician performance metrics       |

#### Parts & Inventory
| Method | Path                              | Description                          |
|--------|-----------------------------------|--------------------------------------|
| GET    | `/api/parts`                      | List parts                           |
| POST   | `/api/parts`                      | Create a part                        |
| GET    | `/api/parts/{part}`               | Show a part                          |
| PUT    | `/api/parts/{part}`               | Update a part                        |
| GET    | `/api/parts/low-stock`            | Parts at or below minimum stock      |
| POST   | `/api/parts/{part}/restock`       | Add stock to a part                  |
| POST   | `/api/parts/usage`                | Record parts usage                   |
| GET    | `/api/parts/suppliers`            | List suppliers                       |
| POST   | `/api/parts/suppliers`            | Create a supplier                    |
| GET    | `/api/parts/purchase-orders`      | List purchase orders                 |
| POST   | `/api/parts/purchase-orders`      | Create a purchase order              |

#### Invoices & Payments
| Method | Path                              | Description                          |
|--------|-----------------------------------|--------------------------------------|
| GET    | `/api/invoices`                   | List invoices                        |
| POST   | `/api/invoices`                   | Generate an invoice                  |
| GET    | `/api/invoices/{invoice}`         | Show an invoice                      |
| POST   | `/api/invoices/{invoice}/pay`     | Record a payment                     |
| POST   | `/api/payments/{payment}/refund`  | Refund a payment                     |

#### Reports
| Method | Path                                 | Description                     |
|--------|--------------------------------------|---------------------------------|
| GET    | `/api/reports/revenue`               | Revenue analytics               |
| GET    | `/api/reports/technicians`           | Technician performance          |
| GET    | `/api/reports/popular-repairs`       | Popular repair services ranking |
| GET    | `/api/reports/inventory`             | Inventory valuation             |
| GET    | `/api/reports/export/{type}`         | Export report data (`csv`/`xlsx`) |

---

## Background Jobs & Notifications

Side effects are decoupled from HTTP requests and processed by the queue worker:

| Job                   | Trigger                                              | Effect                                        |
|-----------------------|------------------------------------------------------|-----------------------------------------------|
| `SendStatusUpdate`    | Work order status change (non-completion)            | Customer notified of new status               |
| `SendCompletionAlert` | Work order marked completed                          | Customer notified the device is ready         |
| `GenerateInvoice`     | Invoice generation                                   | Invoice persistence off the request path      |
| `CheckLowStock`       | Periodic / post-usage stock check                    | Low-stock alert via `NotificationService`     |

The `NotificationService` currently logs messages (with a clear SMS/email integration point) and respects `MAIL_MAILER=log` in development. Queued jobs run on the queue worker; in local development, the `dev` script starts `queue:listen` automatically.

---

## Testing

The test suite runs against an **in-memory SQLite** database with `RefreshDatabase`, so no external services are required.

```bash
# Inside the container
docker compose exec app php artisan test

# Host equivalent (requires local PHP + vendor)
composer test
```

**58 tests / 198 assertions**, covering:

- **Auth** — login, logout, dashboard access, guest redirects.
- **Work orders** — creation, validation, technician assignment, full status workflow, invalid-transition rejection, completion timestamps.
- **Inventory** — part CRUD, stock restocking, low-stock state, purchase order creation and receiving, part usage stock deduction.
- **Billing** — invoice breakdown math (service charge, parts, discounts, tax), invoice generation, partial/full payments, refunds, sequential numbering.
- **Warranties** — registration, listing, revocation, expiry calculation.
- **API** — authentication, token-protected resources, JSON error handling.
- **Unit / Services** — `WorkOrderService`, `PartService`, and `BillingService` domain rules in isolation.

Run a subset with:

```bash
docker compose exec app php artisan test tests/Feature/WorkOrderManagementTest.php
```

---

## Project Structure

```
app/
├── Enums/                  # Domain enums (WorkOrderStatus, InvoiceStatus, ...)
├── Events/                 # LowStockAlert
├── Exceptions/             # InsufficientStockException
├── Http/
│   ├── Controllers/
│   │   ├── Web/            # Browser controllers
│   │   └── Api/            # JSON API controllers (Sanctum)
│   └── Requests/           # FormRequest validation (e.g. StoreWarrantyRequest)
├── Jobs/                   # Queued jobs (SendStatusUpdate, SendCompletionAlert, ...)
├── Models/                 # Eloquent models
├── Services/               # Domain logic (WorkOrderService, PartService, BillingService, ...)
bootstrap/app.php           # Laravel application bootstrap & middleware config
config/                     # Framework and package configuration
database/
├── factories/              # 8 model factories
├── migrations/             # 26 schema migrations
└── seeders/                # Demo data (users, catalog, settings)
routes/
├── web.php                 # Browser routes
├── api.php                 # JSON API routes
└── console.php             # Console commands
resources/
├── views/                  # Blade templates
├── css/                    # Tailwind CSS source
└── js/                     # Alpine.js entrypoints
tests/
├── Feature/                # Feature & integration tests
└── Unit/Services/          # Service unit tests
Dockerfile                  # PHP-FPM + Nginx + Supervisor image
docker-compose.yml          # app, MySQL, Redis, phpMyAdmin
```

---

## Database Schema

26 migrations describe a normalized schema with foreign-key integrity and cascade/null-on-delete rules:

| Table                       | Purpose                                        |
|-----------------------------|------------------------------------------------|
| `users`, `sessions`, `password_reset_tokens` | Authentication & session state |
| `personal_access_tokens`    | Sanctum API tokens                            |
| `roles`, `permissions`, `model_has_roles` | spatie/laravel-permission authorization tables |
| `settings`                  | Runtime business configuration                |
| `device_types`, `brands`, `repair_services` | Reference data for the service catalog |
| `customers`                 | Customer profiles                             |
| `customer_devices`          | Devices registered per customer               |
| `technicians`               | Repair staff                                  |
| `work_orders`               | Repair requests (status, priority, costs)     |
| `work_order_status_history` | Immutable audit trail of status changes       |
| `work_order_notes`          | Notes attached to work orders                 |
| `technician_assignments`    | Technician assignment trail                   |
| `suppliers`, `part_categories`, `parts` | Inventory catalog                    |
| `purchase_orders`, `purchase_order_items` | Procurement lifecycle              |
| `part_usage`                | Parts consumed per work order                 |
| `stock_movements`           | Audit ledger for inventory changes            |
| `invoices`, `payments`      | Billing records                               |
| `warranties`                | Post-repair warranty coverage                 |

---

## Performance & Security

- **Atomicity:** every state-changing flow runs inside a database transaction, so partial failures roll back cleanly.
- **Queued work:** notification and invoice side effects run off the request path via the queue worker.
- **CSRF protection:** enabled for all web routes; API uses Sanctum bearer tokens.
- **Validation:** all inputs are validated server-side via Form Requests and the service layer.
- **Mass-assignment protection:** Eloquent `fillable` attributes are whitelisted (many via PHP attributes).
- **Parameterized queries:** the Eloquent query builder guards against SQL injection.
- **Caching:** config, routes, and views are cached in production (`php artisan optimize`).
- **Container isolation:** services are network-isolated behind a bridge network; credentials are injected via environment.
- **Auditability:** status history, technician assignments, and stock movements provide complete operational traceability.

---

## Roadmap

- Email/SMS notification delivery (wire `NotificationService` to a real provider).
- Schedule `CheckLowStock` on the task scheduler for automated low-stock alerts.
- Role-based UI permissions (enforce `admin` vs `staff` capabilities in middleware and views).
- Multi-branch / multi-location support.
- Barcode scanning for parts intake and device check-in.
- Customer-facing portal for repair status tracking.
- Full internationalization (i18n) beyond the current English/BDT defaults.

---

## Troubleshooting

**The site returns a 419 after login.**
Clear your browser cookies for `localhost:8086` — this usually indicates a stale session after an app-key change.

**I changed `.env`, but the app doesn't pick it up.**
Clear the cached config: `docker compose exec app php artisan config:clear`, then restart the app container.

**I added a Composer/NPM dependency, but it's not installed.**
Named volumes hold `vendor/` and `node_modules/`. Rebuild the image: `docker compose up -d --build`.

**Tests fail with "APP_ENV" / environment mismatch.**
The suite forces the `testing` environment regardless of the OS-level environment. See `tests/TestCase.php` for the reconciliation logic and `phpunit.xml` for the in-memory SQLite configuration.

**Ports 8086/8087 are already in use.**
Change the host-side mapping in `docker-compose.yml` (e.g., `"8088:80"`) and update `APP_URL` in `.env`.

---

## Contributing

Contributions are welcome. Please follow the existing conventions:

1. Run `vendor/bin/pint` to match the project's code style.
2. Add or update tests for any behavior change.
3. Ensure the full suite passes: `docker compose exec app php artisan test`.
4. Keep the service layer as the home for business rules; controllers stay thin.

---

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
