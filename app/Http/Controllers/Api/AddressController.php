<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return AddressResource::collection($request->user()->addresses()->get());
    }

    public function show(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);

        return new AddressResource($address);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;

        $address = Address::create($data);
        $this->syncDefault($request->user()->id, $address, $request->boolean('is_default'));

        return (new AddressResource($address->fresh()))->response()->setStatusCode(201);
    }

    public function update(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);

        $address->update($this->validated($request));
        $this->syncDefault($request->user()->id, $address, $request->boolean('is_default'));

        return new AddressResource($address->fresh());
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);
        $address->delete();

        return response()->json(['message' => 'Address removed.']);
    }

    public function makeDefault(Request $request, Address $address)
    {
        $this->authorizeOwner($request, $address);
        $this->syncDefault($request->user()->id, $address, true);

        return new AddressResource($address->fresh());
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

        if ($makeDefault || ! $hasAnyDefault) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        }
    }

    private function authorizeOwner(Request $request, Address $address): void
    {
        // 404 rather than 403 so address ids of other customers aren't confirmed.
        abort_unless($address->user_id === $request->user()->id, 404);
    }
}
