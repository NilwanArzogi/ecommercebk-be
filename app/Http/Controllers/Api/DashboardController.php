<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller {
    public function index() {
        $totalProducts = Product::count();
        $totalOrders   = Order::count();
        $pendingOrders = Order::where('status', 'Menunggu')->count();
        $totalRevenue  = Order::where('status', 'Selesai')->sum('total_price');

        $topProducts = Product::orderBy('sold_count', 'desc')
            ->take(5)
            ->get(['id', 'name', 'price', 'sold_count', 'image']);

        $recentOrders = Order::with('items')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'total_products'  => $totalProducts,
            'total_orders'    => $totalOrders,
            'pending_orders'  => $pendingOrders,
            'total_revenue'   => $totalRevenue,
            'top_products'    => $topProducts,
            'recent_orders'   => $recentOrders,
        ]);
    }
}