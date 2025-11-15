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
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['variants', 'images'])->get();
        return response()->json($products);
    }
    public function show($id)
    {
        return Product::with(['variant', 'image'])->findOrFail($id);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'price' => 'required|decimal',
            'category_id' => 'required|integer|exists:categories,category_id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'variants' => 'required|array',
        ]);
    }
}
