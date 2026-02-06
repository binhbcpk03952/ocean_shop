<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return response()->json($brands);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
            'logo' => 'nullable', // Image file or string URL
        ]);

        try {
            $logoPath = null;

            if ($request->hasFile('logo')) {
                $request->validate([
                    'logo' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                ]);
                $file = $request->file('logo');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $logoPath = $file->storeAs('images/brands', $fileName, 'public');
            } elseif ($request->filled('logo') && is_string($request->logo)) {
                // Check if base64
                if (preg_match('/^data:image\/(\w+);base64,/', $request->logo, $type)) {
                    $data = substr($request->logo, strpos($request->logo, ',') + 1);
                    $data = base64_decode($data);
                    if ($data === false) {
                        throw new \Exception('Invalid base64 image data.');
                    }
                    $extension = strtolower($type[1]);
                    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                        throw new \Exception('Unsupported image type.');
                    }
                    $imageName = time() . '_' . Str::random(6) . '.' . $extension;
                    $logoPath = 'images/brands/' . $imageName;
                    Storage::disk('public')->put($logoPath, $data);
                } elseif (filter_var($request->logo, FILTER_VALIDATE_URL)) {
                    $logoPath = $request->logo;
                }
            }

            $brand = Brand::create([
                'name' => $request->name,
                'description' => $request->description,
                'logo' => $logoPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm thương hiệu thành công',
                'data' => $brand
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi thêm thương hiệu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        return response()->json($brand);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $id . ',brand_id',
            'description' => 'nullable|string',
            'logo' => 'nullable',
        ]);

        try {
            $logoPath = $brand->logo;

            if ($request->hasFile('logo')) {
                $request->validate([
                    'logo' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                ]);
                // Delete old logo if exists and is local
                if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                    Storage::disk('public')->delete($brand->logo);
                }

                $file = $request->file('logo');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $logoPath = $file->storeAs('images/brands', $fileName, 'public');
            } elseif ($request->filled('logo') && is_string($request->logo) && $request->logo !== $brand->logo) {
                // Check if base64
                if (preg_match('/^data:image\/(\w+);base64,/', $request->logo, $type)) {
                    // Delete old
                    if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                        Storage::disk('public')->delete($brand->logo);
                    }

                    $data = substr($request->logo, strpos($request->logo, ',') + 1);
                    $data = base64_decode($data);
                    $extension = strtolower($type[1]);
                    $imageName = time() . '_' . Str::random(6) . '.' . $extension;
                    $logoPath = 'images/brands/' . $imageName;
                    Storage::disk('public')->put($logoPath, $data);
                } elseif (filter_var($request->logo, FILTER_VALIDATE_URL)) {
                    $logoPath = $request->logo;
                }
            }

            $brand->update([
                'name' => $request->name,
                'description' => $request->description,
                'logo' => $logoPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thương hiệu thành công',
                'data' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cập nhật thương hiệu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        // Check if brand has products
        if ($brand->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa thương hiệu vì đã có sản phẩm thuộc thương hiệu này.'
            ], 400);
        }

        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa thương hiệu thành công'
        ]);
    }
}
