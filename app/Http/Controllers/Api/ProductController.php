<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
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
        $query = Product::with(['variant', 'image', 'categories']);

        // // --- FILTER THEO GIÁ ---
        // if ($request->price_range) {
        //     switch ($request->price_range) {
        //         case 'under_100':
        //             $query->where('price', '<', 100000);
        //             break;
        //         case '100_1m':
        //             $query->whereBetween('price', [100000, 1000000]);
        //             break;
        //         case '1m_5m':
        //             $query->whereBetween('price', [1000000, 5000000]);
        //             break;
        //         case 'over_5m':
        //             $query->where('price', '>', 5000000);
        //             break;
        //     }
        // }

        // // --- FILTER THEO DANH MỤC ---
        // if ($request->category_id) {
        //     $query->where('category_id', $request->category_id);
        // }

        // // --- FILTER THEO SIZE ---
        // if ($request->size) {
        //     $query->whereHas('variant', function ($q) use ($request) {
        //         $q->where('size', $request->size);
        //     });
        // }

        // // --- FILTER ƯU ĐÃI ---
        // if ($request->filter === 'sale') {
        //     $query->whereNotNull('sale_price')
        //         ->whereColumn('sale_price', '<', 'price');
        // }

        // // --- FILTER MỚI NHẤT ---
        // if ($request->filter === 'latest') {
        //     $query->orderBy('created_at', 'desc');
        // }

        return response()->json($query->get());
    }

    public function show($id)
    {
        return Product::with(['variant', 'image', 'categories'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        // 1. Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',

            // Frontend đã gửi variants dạng JSON string
            'variants' => 'required|json',

            // Validate mảng ảnh theo cấu trúc variant_images[index][]
            'variant_images' => 'nullable|array',
            'variant_images.*' => 'nullable|array',
            'variant_images.*.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5000',
        ]);

        // Sử dụng Transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();

        try {
            // 2. Tạo Product cơ bản
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
            ]);

            // 3. Xử lý Variants & Images
            $variantsData = json_decode($request->variants, true); // Decode JSON thông tin size/stock/color
            $variantImageGroups = $request->file('variant_images', []); // Lấy file ảnh

            // Duyệt qua từng nhóm biến thể (Từng màu)
            foreach ($variantsData as $index => $groupData) {

                $createdVariantIds = []; // Mảng chứa các ID của các size thuộc màu này

                // A. Tạo các dòng trong bảng variants (Mỗi size là 1 dòng record)
                foreach ($groupData['sizes'] as $sizeItem) {
                    $newVariant = $product->variant()->create([
                        'color' => $groupData['color'],
                        'size'  => $sizeItem['size'],
                        'stock' => $sizeItem['stock'],
                        // Nếu user không nhập giá riêng (hoặc = 0) thì lấy giá chung
                        'price' => ($sizeItem['price'] && $sizeItem['price'] > 0) ? $sizeItem['price'] : $product->price,
                    ]);

                    $createdVariantIds[] = $newVariant->variant_id;
                }

                // B. Xử lý ảnh cho nhóm màu này (Gắn ảnh cho TẤT CẢ các size vừa tạo)
                if (isset($variantImageGroups[$index])) {
                    foreach ($variantImageGroups[$index] as $imgKey => $file) {

                        // Tạo tên file duy nhất: time_slug-ten-anh.jpg
                        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

                        // Lưu file vào disk
                        $path = $file->storeAs('images/products', $fileName, 'public');

                        // Xác định ảnh chính: Ảnh đầu tiên trong mảng (index 0) là ảnh chính
                        $isMain = ($imgKey === 0) ? 1 : 0;

                        // Lưu đường dẫn ảnh vào DB cho từng variant_id (size)
                        foreach ($createdVariantIds as $variantId) {
                            $product->image()->create([
                                'product_id' => $product->product_id,
                                'variant_id' => $variantId,
                                'image_url'  => $path,
                                'is_main'    => $isMain,
                            ]);
                        }
                    }
                }
            }

            DB::commit(); // Lưu tất cả vào DB nếu không có lỗi

            return response()->json([
                'status' => true,
                'message' => 'Thêm sản phẩm thành công!',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác nếu có lỗi, xóa sạch dữ liệu rác vừa tạo

            // Xóa ảnh nếu lỡ upload lên rồi mà lỗi DB (Optional - xử lý kỹ hơn thì cần đoạn này)

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function relatedProducts($id)
{
    // Lấy sản phẩm gốc
    $product = Product::findOrFail($id);

    // Lấy sản phẩm liên quan cùng danh mục
    $related = Product::whereHas('categories', function($query) use ($product) {
        $query->whereIn('category_id', $product->categories->pluck('category_id'));
    })
    ->where('product_id', '!=', $product->product_id)
    ->with(['variant', 'image', 'categories'])
    ->limit(4)
    ->get();

    return response()->json([
        'success' => true,
        'data' => $related
    ]);
}


}
