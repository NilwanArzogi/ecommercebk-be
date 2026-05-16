<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller {

    public function index(Request $request) {
        $query = Product::where('is_active', true);
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        return response()->json($query->latest()->get());
    }

    public function show($id) {
        return response()->json(Product::findOrFail($id));
    }

    public function adminIndex() {
        return response()->json(Product::latest()->get());
    }

    public function store(Request $request) {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'category'    => $request->category,
            'image'       => $imagePath,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'category'    => $request->category,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return response()->json($product);
    }

    public function destroy($id) {
        $product = Product::findOrFail($id);
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return response()->json(['message' => 'Produk dihapus']);
    }
}