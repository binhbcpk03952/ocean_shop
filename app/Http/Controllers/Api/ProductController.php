<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use GuzzleHttp\Handler\Proxy;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['variant', 'image', 'categories'])->get();
        return response()->json($products);
    }
    public function show($id)
    {
        return Product::with(['variant', 'image', 'categories'])->findOrFail($id);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5000',
            'variant_images' => 'nullable|array',
            'variant_images.*' => 'nullable|array',
            'variant_images.*.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5000',
            'variants' => 'required|json',
        ]);
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $file) {
                $imageName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs('images/products', $imageName, 'public');
                $product->image()->create([
                    'product_id' => $product->product_id,
                    'variant_id' => null,
                    'image_url' => $imagePath,
                    'is_main' => ($key == 0) ? 1 : 0,
                ]);
            }
        }
        $variantsData = json_decode($request->variants, true);
        $variantImageGroups = $request->file('variant_images', []);

        foreach ($variantsData as $index => $groupData) {

            $createdIds = []; // Mảng chứa các variant_id vừa tạo cho màu này

            // A. Tạo các dòng trong bảng variants (Mỗi size là 1 dòng)
            foreach ($groupData['sizes'] as $sizeItem) {
                $newVariant = $product->variant()->create([
                    'color' => $groupData['color'], // "Trắng/Xanh"
                    'size'  => $sizeItem['size'],   // "41" rồi đến "42"
                    'stock' => $sizeItem['stock'],
                    'price' => $sizeItem['price'] ?? $product->price, // Dùng giá riêng hoặc giá gốc
                ]);

                // Lưu lại ID vừa tạo (VD: 1, 2)
                $createdIds[] = $newVariant->variant_id;
            }

            // B. Lưu ảnh và gắn cho TẤT CẢ các ID vừa tạo ở trên
            if (isset($variantImageGroups[$index])) {
                foreach ($variantImageGroups[$index] as $file) {
                    // Lưu file vật lý 1 lần
                    $path = $file->storeAs('images/products', $file->getClientOriginalName(), 'public');

                    // Tạo bản ghi trong DB cho từng variant_id
                    foreach ($createdIds as $variantId) {
                        $product->image()->create([
                            'product_id' => $product->product_id,
                            'image_url' => $path,
                            'variant_id' => $variantId,
                            'is_main' => 0,
                        ]);
                    }
                }
            }
        }
    }
}