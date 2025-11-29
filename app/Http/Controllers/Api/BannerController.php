<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        return Banner::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'link' => 'nullable|string',
        ]);

        $path = null;
        if ($request->hasFile('images')) {
            $file = $request->file('images');
            $imageName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('images/banners', $imageName, 'public');
        }
        $user_id = $request->user()->user_id;

        $banner = Banner::create([
            'user_id' => $user_id,
            'images' => $path,
            'link' => $request->link,
        ]);

        return response()->json($banner, 201);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->images);
            $banner->images = $request->file('image')->store('banners', 'public');
        }

        if ($request->link !== null) {
            $banner->link = $request->link;
        }

        $banner->save();

        return response()->json($banner);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        Storage::disk('public')->delete($banner->images);
        $banner->delete();

        return response()->json(['message' => 'Deleted', 'status' => true]);
    }
}