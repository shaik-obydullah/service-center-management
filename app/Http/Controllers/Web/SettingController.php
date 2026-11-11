<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\DeviceType;
use App\Models\PartCategory;
use App\Models\RepairService;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $deviceTypes = DeviceType::with('brands')->get();
        $partCategories = PartCategory::all();
        $repairServices = RepairService::with('deviceType')->get();

        return view('settings.index', compact('settings', 'deviceTypes', 'partCategories', 'repairServices'));
    }

    public function update(Request $request)
    {
        $payload = $request->only([
            'shop_name', 'address', 'phone', 'email', 'currency_symbol',
            'tax_rate', 'invoice_footer',
        ]);

        foreach ($payload as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'general']);
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'Settings saved.');
    }

    public function storeDeviceType(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        DeviceType::create(['name' => $request->name]);

        return back()->with('success', 'Device type added.');
    }

    public function storeBrand(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'device_type_id' => ['nullable', 'exists:device_types,id'],
        ]);

        Brand::create($request->only('name', 'device_type_id'));

        return back()->with('success', 'Brand added.');
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        PartCategory::create(['name' => $request->name]);

        return back()->with('success', 'Part category added.');
    }

    public function storeRepairService(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'device_type_id' => ['nullable', 'exists:device_types,id'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_time_hours' => ['nullable', 'integer', 'min:0'],
        ]);

        RepairService::create($request->only('name', 'device_type_id', 'estimated_cost', 'estimated_time_hours'));

        return back()->with('success', 'Repair service added.');
    }
}
