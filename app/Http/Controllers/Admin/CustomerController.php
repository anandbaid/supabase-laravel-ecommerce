<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Throwable;

class CustomerController extends Controller
{
    public function __construct(private readonly SupabaseAuthService $supabaseAuth)
    {
    }

    public function index(Request $request)
    {
        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        abort_unless($customer->role === 'customer', 404);
        $orders = $customer->orders()->latest()->get();
        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $supabaseUser = $this->supabaseAuth->adminCreateUser($data['email'], $data['password'], [
                'name' => $data['name'],
            ]);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['email' => $e->getMessage()]);
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['email' => 'Something went wrong creating the account. Please try again.']);
        }

        $customer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => null,
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'supabase_uid' => $supabaseUser['id'] ?? null,
        ]);

        return redirect()->route('admin.customers.index')->with('success', "Account for {$customer->name} created.");
    }

    public function edit(User $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($customer->id)],
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $emailChanged = $data['email'] !== $customer->email;
        $passwordChanged = ! empty($data['password']);

        if (($emailChanged || $passwordChanged) && $customer->supabase_uid) {
            try {
                $this->supabaseAuth->adminUpdateUser($customer->supabase_uid, [
                    'email' => $emailChanged ? $data['email'] : null,
                    'password' => $passwordChanged ? $data['password'] : null,
                    'email_confirm' => $emailChanged ? true : null,
                ]);
            } catch (RuntimeException $e) {
                return back()->withInput()->withErrors(['email' => $e->getMessage()]);
            } catch (Throwable $e) {
                return back()->withInput()->withErrors(['email' => 'Something went wrong updating the account. Please try again.']);
            }
        }

        $customer->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('admin.customers.index')->with('success', "Account for {$customer->name} updated.");
    }

    public function destroy(User $customer)
    {
        abort_if($customer->id === auth()->id(), 403, "You can't delete your own account while logged in.");

        if ($customer->supabase_uid) {
            $this->supabaseAuth->adminDeleteUser($customer->supabase_uid);
        }

        $customer->delete();

        return back()->with('success', 'Account deleted.');
    }
}
