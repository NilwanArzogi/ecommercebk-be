<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    public function store(Request $request) {
        $request->validate([
            'customer_name'    => 'required|string',
            'customer_phone'   => 'required|string',
            'customer_address' => 'nullable|string',
            'payment_method'   => 'required|in:transfer,cod,ewallet',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'notes'            => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                    'subtotal'   => $subtotal,
                ];
                $product->decrement('stock', $item['quantity']);
                $product->increment('sold_count', $item['quantity']);
            }

            $order = Order::create([
                'customer_name'    => $request->customer_name,
                'customer_phone'   => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'total_price'      => $totalPrice,
                'payment_method'   => $request->payment_method,
                'notes'            => $request->notes,
                'status'           => 'Menunggu',
            ]);

            $order->items()->createMany($orderItems);
            DB::commit();

            return response()->json($order->load('items.product'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pesanan', 'error' => $e->getMessage()], 500);
        }
    }

    public function index() {
        $orders = Order::with('items.product')->latest()->get();
        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id) {
        $request->validate(['status' => 'required|in:Menunggu,Diproses,Selesai']);
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        return response()->json($order);
    }

    public function show($id) {
        $order = Order::with('items.product')->findOrFail($id);
        return response()->json($order);
    }
}