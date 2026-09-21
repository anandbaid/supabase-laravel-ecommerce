<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $taxRate = Setting::taxRate();
        return view('admin.settings.tax', compact('taxRate'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::set('tax_rate', $data['tax_rate']);

        return back()->with('success', 'Tax rate updated.');
    }
}
