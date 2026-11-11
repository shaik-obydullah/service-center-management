<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\DeviceType;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\RepairService;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->seedSettings();

        $this->seedCatalog();

        $supplier = Supplier::create([
            'name' => 'Tech Parts Trading',
            'contact_person' => 'Karim Ahmed',
            'phone' => '+8801700000001',
            'email' => 'sales@techparts.example',
            'address' => '30 Motijheel, Dhaka',
            'status' => true,
        ]);

        $screenCategory = PartCategory::where('name', 'Screens')->first();
        $batteryCategory = PartCategory::where('name', 'Batteries')->first();

        Part::create([
            'part_category_id' => $screenCategory->id,
            'supplier_id' => $supplier->id,
            'name' => 'iPhone 13 LCD Screen',
            'code' => 'SCR-IP13',
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'cost_price' => 4500,
            'selling_price' => 5500,
            'quantity' => 20,
            'minimum_stock' => 5,
            'status' => 'active',
        ]);

        Part::create([
            'part_category_id' => $batteryCategory->id,
            'supplier_id' => $supplier->id,
            'name' => 'Samsung Battery 5000mAh',
            'code' => 'BAT-SM50',
            'brand' => 'Samsung',
            'model' => 'Galaxy A52',
            'cost_price' => 1200,
            'selling_price' => 1800,
            'quantity' => 2,
            'minimum_stock' => 10,
            'status' => 'active',
        ]);

        Part::create([
            'part_category_id' => $screenCategory->id,
            'supplier_id' => $supplier->id,
            'name' => 'Galaxy A52 Screen',
            'code' => 'SCR-GA52',
            'brand' => 'Samsung',
            'model' => 'Galaxy A52',
            'cost_price' => 2800,
            'selling_price' => 3600,
            'quantity' => 8,
            'minimum_stock' => 3,
            'status' => 'active',
        ]);

        Technician::create([
            'employee_id' => 'TEC-001',
            'name' => 'Rahim Uddin',
            'phone' => '+8801700000002',
            'email' => 'rahim@example.com',
            'skills_json' => ['Screen Replacement', 'Battery', 'Diagnostics'],
            'hourly_rate' => 300,
            'status' => 'active',
        ]);

        Technician::create([
            'employee_id' => 'TEC-002',
            'name' => 'Fatema Begum',
            'phone' => '+8801700000003',
            'email' => 'fatema@example.com',
            'skills_json' => ['Board Repair', 'Microsoldering'],
            'hourly_rate' => 450,
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'name' => 'Shakil Hossain',
            'phone' => '+8801700000004',
            'email' => 'shakil@example.com',
            'city' => 'Dhaka',
            'address' => 'Dhanmondi, Dhaka',
            'contact_preference' => 'phone',
            'loyalty_member' => true,
        ]);

        $deviceTypeSmartphone = DeviceType::where('name', 'Smartphone')->first();

        $customer->devices()->create([
            'device_type_id' => $deviceTypeSmartphone?->id,
            'type' => 'Phone',
            'brand' => 'Samsung',
            'model' => 'Galaxy A52',
            'serial_number' => 'SM-A525F-0001',
            'color' => 'Black',
        ]);

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'staff']);
        $admin->assignRole('admin');
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['shop_name', 'Service Center', 'general'],
            ['address', 'Dhanmondi, Dhaka', 'general'],
            ['phone', '+8801700000000', 'general'],
            ['email', 'info@servicecenter.example', 'general'],
            ['currency_symbol', '৳', 'general'],
            ['tax_rate', '5', 'general'],
            ['invoice_footer', 'Thank you for choosing our service center.', 'general'],
            ['default_warranty_days', '30', 'general'],
        ];

        foreach ($settings as [$key, $value, $group]) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
    }

    protected function seedCatalog(): void
    {
        $smartphone = DeviceType::updateOrCreate(['name' => 'Smartphone'], ['status' => true]);
        $laptop = DeviceType::updateOrCreate(['name' => 'Laptop'], ['status' => true]);
        $tablet = DeviceType::updateOrCreate(['name' => 'Tablet'], ['status' => true]);

        foreach (['Apple', 'Samsung', 'Xiaomi', 'Dell', 'HP'] as $name) {
            Brand::updateOrCreate(['name' => $name], ['device_type_id' => $smartphone->id]);
        }

        foreach (['Screens', 'Batteries', 'Charging Ports', 'Camera Modules', 'Logic Boards'] as $name) {
            PartCategory::updateOrCreate(['name' => $name], ['status' => true]);
        }

        $services = [
            ['Screen Replacement', $smartphone->id, 3500, 2],
            ['Battery Replacement', $smartphone->id, 1500, 1],
            ['Charging Port Repair', $smartphone->id, 1200, 2],
            ['Software / OS Install', null, 800, 1],
            ['Logic Board Repair', $smartphone->id, 5000, 24],
            ['Data Recovery', null, 2500, 4],
        ];

        foreach ($services as [$name, $deviceTypeId, $cost, $hours]) {
            RepairService::updateOrCreate(
                ['name' => $name],
                ['device_type_id' => $deviceTypeId, 'estimated_cost' => $cost, 'estimated_time_hours' => $hours, 'status' => true]
            );
        }
    }
}
