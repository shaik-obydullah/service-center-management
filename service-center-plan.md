# Service Center Management System - Laravel 13

## Overview

Modern service center management system built with Laravel 13 for repair shops (phones, laptops, cars, appliances). Handles work orders, technician assignments, spare parts, and customer notifications.

**Original:** https://github.com/skarnov/service-center-management-system (CodeIgniter 2.x)
**New:** Laravel 13 + Blade + Alpine.js + Tailwind CSS 4

---

## Why This Project Matters

| Aspect | Impact |
|--------|--------|
| Industry | Automotive / Electronics Repair |
| Complexity | High (work orders, inventory, tracking) |
| Real-world | Repair shop operations |
| Skills | Workflow management, inventory, notifications |

---

## Core Features

### 1. Work Order Management
- Create work orders
- Device/product registration
- Problem description
- Priority levels
- Status tracking
- Cost estimation

### 2. Customer Management
- Customer profiles
- Device history
- Contact preferences
- Loyalty tracking

### 3. Technician Management
- Technician profiles
- Skill assignment
- Workload tracking
- Performance metrics

### 4. Spare Parts Inventory
- Parts catalog
- Stock management
- Supplier tracking
- Auto-reorder alerts
- Part usage tracking

### 5. Billing & Payments
- Service charges
- Parts costs
- Invoice generation
- Payment tracking
- Warranty management

### 6. Notifications
- Work order updates
- Status changes
- Completion alerts
- SMS/Email notifications

### 7. Reports & Analytics
- Revenue reports
- Technician performance
- Popular repairs
- Inventory reports

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Blade + Alpine.js + Tailwind CSS 4 |
| Database | MySQL 8.0 |
| Auth | Laravel Sanctum + Spatie Permissions |
| Queue | Redis (notifications) |
| Exports | Laravel Excel / DomPDF |
| Docker | Docker + Docker Compose |
| Testing | PHPUnit + Pest |
| CI/CD | GitHub Actions |

---

## Database Schema

### Core Tables (25+ tables)

#### Customers
```sql
customers (id, name, email, phone, address, city,
           nid_number, timestamps)

customer_devices (id, customer_id, type, brand, model,
                  serial_number, color, notes, timestamps)
```

#### Work Orders
```sql
work_orders (id, order_number, customer_id, device_id,
             technician_id, problem_description,
             priority, status, estimated_cost,
             actual_cost, estimated_date, completed_at,
             created_by, timestamps)

work_order_status_history (id, work_order_id, status,
                           changed_by, notes, timestamps)

work_order_notes (id, work_order_id, user_id, note,
                  timestamps)
```

#### Technicians
```sql
technicians (id, user_id, employee_id, name, phone,
             email, skills_json, hourly_rate, status,
             timestamps)

technician_assignments (id, work_order_id, technician_id,
                        assigned_at, completed_at, 
                        hours_spent, timestamps)
```

#### Spare Parts
```sql
parts (id, name, code, category, brand, model,
       cost_price, selling_price, quantity,
       minimum_stock, status, timestamps)

part_categories (id, name, status, timestamps)

suppliers (id, name, contact_person, phone, email,
           address, status, timestamps)

purchase_orders (id, supplier_id, order_date,
                 status, total_amount, timestamps)

purchase_order_items (id, purchase_order_id, part_id,
                      quantity, unit_price, total,
                      timestamps)

part_usage (id, work_order_id, part_id, quantity,
            unit_price, total, timestamps)

stock_movements (id, part_id, type, quantity, reference,
                 notes, timestamps)
```

#### Billing
```sql
invoices (id, work_order_id, invoice_number,
          service_charge, parts_cost, discount,
          tax, total, status, timestamps)

payments (id, invoice_id, amount, method, reference,
          status, timestamps)

warranties (id, work_order_id, part_id, duration_days,
            start_date, end_date, status, timestamps)
```

#### Settings
```sql
settings (id, key, value, group, timestamps)

device_types (id, name, status, timestamps)

brands (id, name, device_type_id, status, timestamps)

repair_services (id, name, device_type_id, estimated_cost,
                 estimated_time_hours, status, timestamps)
```

---

## API Endpoints

### Auth
```
POST   /api/login
POST   /api/logout
GET    /api/user
```

### Customers
```
GET    /api/customers
POST   /api/customers
GET    /api/customers/{id}
PUT    /api/customers/{id}
GET    /api/customers/{id}/devices
POST   /api/customers/{id}/devices
GET    /api/customers/{id}/work-orders
```

### Work Orders
```
GET    /api/work-orders
POST   /api/work-orders
GET    /api/work-orders/{id}
PUT    /api/work-orders/{id}
POST   /api/work-orders/{id}/assign
POST   /api/work-orders/{id}/status
GET    /api/work-orders/{id}/history
GET    /api/work-orders/{id}/notes
POST   /api/work-orders/{id}/notes
```

### Technicians
```
GET    /api/technicians
POST   /api/technicians
GET    /api/technicians/{id}
PUT    /api/technicians/{id}
GET    /api/technicians/{id}/work-orders
GET    /api/technicians/{id}/performance
```

### Parts
```
GET    /api/parts
POST   /api/parts
GET    /api/parts/{id}
PUT    /api/parts/{id}
GET    /api/parts/low-stock
POST   /api/parts/usage
GET    /api/parts/suppliers
POST   /api/parts/purchase-orders
```

### Billing
```
GET    /api/invoices
POST   /api/invoices
GET    /api/invoices/{id}
POST   /api/invoices/{id}/pay
GET    /api/payments
POST   /api/payments/refund
```

### Reports
```
GET    /api/reports/revenue
GET    /api/reports/technicians
GET    /api/reports/popular-repairs
GET    /api/reports/inventory
GET    /api/reports/export/{type}
```

---

## Project Structure

```
service-center/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── WorkOrderController.php
│   │   │   │   ├── TechnicianController.php
│   │   │   │   ├── PartController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   └── ReportController.php
│   │   │   └── Web/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   │   ├── WorkOrderService.php
│   │   ├── PartService.php
│   │   ├── BillingService.php
│   │   ├── NotificationService.php
│   │   └── ReportService.php
│   ├── Jobs/
│   │   ├── SendStatusUpdate.php
│   │   ├── SendCompletionAlert.php
│   │   ├── CheckLowStock.php
│   │   └── GenerateInvoice.php
│   ├── Notifications/
│   └── Enums/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── dashboard/
│       ├── customers/
│       ├── work-orders/
│       ├── technicians/
│       ├── parts/
│       ├── billing/
│       └── reports/
├── routes/
├── tests/
├── docker/
├── docker-compose.yml
├── README.md
└── .github/
    └── workflows/
        └── ci.yml
```

---

## Key Business Logic

### Work Order Flow
```
1. Customer brings device
2. Register customer (if new)
3. Register device (brand, model, serial)
4. Describe problem
5. Create work order
6. Assign technician
7. Technician diagnoses
8. Add parts needed
9. Update estimated cost
10. Customer approves
11. Repair in progress
12. Quality check
13. Ready for pickup
14. Customer picks up
15. Generate invoice
16. Payment received
17. Close work order
```

### Status Flow
```
┌──────────┐    ┌──────────┐    ┌──────────┐
│  New     │───▶│ Diagnosed│───▶│ Approved │
└──────────┘    └──────────┘    └──────────┘
                                     │
                                     ▼
┌──────────┐    ┌──────────┐    ┌──────────┐
│ Completed│◀───│ In Repair│◀───│  Ready   │
└──────────┘    └──────────┘    └──────────┘
```

### Inventory Management
```
function usePart(workOrderId, partId, quantity):
    // Check stock
    part = Part::find(partId)
    if part.quantity < quantity:
        throw new InsufficientStockException()
    
    // Deduct stock
    part.quantity -= quantity
    part.save()
    
    // Log movement
    StockMovement::create([
        'part_id' => partId,
        'type' => 'out',
        'quantity' => quantity,
        'reference' => "WO-{$workOrderId}"
    ])
    
    // Link to work order
    PartUsage::create([
        'work_order_id' => workOrderId,
        'part_id' => partId,
        'quantity' => quantity,
        'unit_price' => part.selling_price,
        'total' => quantity * part.selling_price
    ])
    
    // Check minimum stock
    if part.quantity <= part.minimum_stock:
        event(new LowStockAlert($part))
```

### Invoice Calculation
```
function calculateInvoice(workOrder):
    serviceCharge = workOrder.estimated_cost
    
    partsCost = PartUsage::where('work_order_id', workOrder.id)
                ->sum('total')
    
    subtotal = serviceCharge + partsCost
    
    discount = subtotal * (workOrder.discount / 100)
    
    tax = (subtotal - discount) * (config('tax.rate') / 100)
    
    total = subtotal - discount + tax
    
    return compact('serviceCharge', 'partsCost', 'subtotal', 
                   'discount', 'tax', 'total')
```

---

## Development Timeline: 2.5 Weeks

### Week 1: Foundation
- Day 1-2: Laravel 13 setup, Auth, Roles
- Day 3: Customer management
- Day 4: Device registration
- Day 5: Work order creation

### Week 2: Core Features
- Day 6-7: Work order workflow
- Day 8: Technician management
- Day 9: Spare parts inventory
- Day 10: Billing & payments

### Week 3: Polish
- Day 11: Notifications
- Day 12: Dashboard & reports
- Day 13: Testing (Unit + Feature)
- Day 14: README with screenshots
- Day 15-17: Final testing & deployment

---

## README Highlights

### Badges
- Laravel 13
- PHP 8.3
- MySQL 8.0
- Docker
- PHPUnit

### Features
- Work order management
- Customer & device tracking
- Technician assignments
- Spare parts inventory
- Billing & payments
- Warranty management
- Dockerized setup

### Installation
```bash
git clone https://github.com/shaik-obydullah/service-center.git
cd service-center
composer install
cp .env.example .env
php artisan key:generate
docker-compose up -d
php artisan migrate --seed
php artisan serve
```

### Screenshots
- Work order list
- Technician dashboard
- Parts inventory
- Invoice view

---

## Portfolio Value

### After This Addition (16 Laravel Projects)
```
Healthcare + Business + Data + Real Estate + Government + 
FinTech + Network Marketing + NGO + Crypto Trading + 
EdTech + Transportation + Manufacturing + Telecom + 
Library + Hospitality + SERVICE/REPAIR
= "Senior Laravel developer with extensive industry experience across 16 sectors"
```

### Interview Story
> "I rebuilt a service center management system from CodeIgniter to Laravel 13. It handles work orders, technician assignments, spare parts inventory, and billing for repair shops."

### Skills Demonstrated
| Skill | How It's Shown |
|-------|----------------|
| Laravel 13 | Modern framework features |
| Workflow | Status-based work orders |
| Inventory | Parts management |
| Billing | Invoice generation |
| Notifications | Status updates |
| Docker | Production-ready setup |

---

## Comparison: Old vs New

| Aspect | Old (CodeIgniter) | New (Laravel 13) |
|--------|-------------------|------------------|
| Framework | CI 2.x (EOL) | Laravel 13 |
| Work Orders | Basic | Full workflow |
| Inventory | Basic | Auto-reorder |
| Billing | Basic | Full invoicing |
| Auth | Basic | Sanctum + Roles |
| Reports | Basic | Advanced analytics |
| Testing | None | PHPUnit + Pest |
| Security | Vulnerable | Production-ready |

---

## Ready to Build?

When ready, run:
```bash
# Create Laravel 13 project
composer create-project laravel/laravel service-center

# Install dependencies
composer require spatie/laravel-permission
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf

# Install Tailwind CSS + Alpine.js
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```
