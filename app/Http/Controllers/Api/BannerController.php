<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller {
    public function index() {
        return response()->json(Banner::where('is_active', true)->orderBy('order')->get());
    }

    public function adminIndex() {
        return response()->json(Banner::orderBy('order')->get());
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string',
            'image' => 'required|image|max:4096',
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'title'       => $request->title,
            'description' => $request->description,
            'image'       => $imagePath,
            'is_active'   => $request->boolean('is_active', true),
            'order'       => $request->order ?? 0,
        ]);

        return response()->json($banner, 201);
    }

    public function update(Request $request, $id) {
        $banner = Banner::findOrFail($id);
        $request->validate(['title' => 'required|string']);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image);
            $banner->image = $request->file('image')->store('banners', 'public');
        }

        $banner->update([
            'title'       => $request->title,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'order'       => $request->order ?? $banner->order,
        ]);

        return response()->json($banner);
    }

    public function destroy($id) {
        $banner = Banner::findOrFail($id);
        Storage::disk('public')->delete($banner->image);
        $banner->delete();
        return response()->json(['message' => 'Banner dihapus']);
    }
}