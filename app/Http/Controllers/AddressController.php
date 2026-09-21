<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->get();
        return view('account.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('account.addresses.form', ['address' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;

        $address = Address::create($data);
        $this->syncDefault($request->user()->id, $address, $request->boolean('is_default'));

        return redirect()->route('addresses.index')->with('success', 'Address added.');
    }

    public function edit(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);
        return view('account.addresses.form', compact('address'));
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);

        $data = $this->validated($request);
        $address->update($data);
        $this->syncDefault($request->user()->id, $address, $request->boolean('is_default'));

        return redirect()->route('addresses.index')->with('success', 'Address updated.');
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);
        $address->delete();

        return back()->with('success', 'Address removed.');
    }

    public function makeDefault(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);
        $this->syncDefault($request->user()->id, $address, true);

        return back()->with('success', 'Default address updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:120'],
        ]) + ['label' => $request->input('label') ?: 'Home'];
    }

    private function syncDefault(int $userId, Address $address, bool $makeDefault): void
    {
        $hasAnyDefault = Address::where('user_id', $userId)->where('is_default', true)->exists();

        if ($makeDefault || !$hasAnyDefault) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        }
    }

    private function authorizeOwner(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 403);
    }
}
