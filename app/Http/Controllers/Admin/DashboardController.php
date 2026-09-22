<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard stats don't need to be millisecond-fresh — cache for 60s
        // so repeated admin page loads (or several admins browsing at once)
        // don't each pay for 8 round trips to the remote database.
        $data = Cache::remember('admin.dashboard.stats', 60, function () {
            $totalSales = Order::where('payment_status', 'paid')->sum('total');
            $totalOrders = Order::count();
            $totalCustomers = User::where('role', 'customer')->count();
            $totalProducts = Product::count();

            $recentOrders = Order::latest()->take(5)->get();

            $recentCustomers = User::where('role', 'customer')
                ->withCount('orders')
                ->withSum('orders as total_spent', 'total')
                ->latest()->take(5)->get();

            $topProducts = DB::table('order_items')
                ->select('product_name', DB::raw('SUM(quantity) as sold'), DB::raw('SUM(subtotal) as revenue'))
                ->groupBy('product_name')
                ->orderByDesc('sold')
                ->take(5)
                ->get();

            $salesChart = Order::selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return compact(
                'totalSales', 'totalOrders', 'totalCustomers', 'totalProducts',
                'recentOrders', 'recentCustomers', 'topProducts', 'salesChart'
            );
        });

        return view('admin.dashboard', $data);
    }
}
