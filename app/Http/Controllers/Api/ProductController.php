<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function getNewProduct()
    {
        $query = Product::query()->with(['variant', 'image', 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        return response()->json($query);
    }

    public function index(Request $request)
    {
        $query = Product::query()
            ->with([
                'variant',
                'image',
                'categories',
                'brand'
            ]);

        /* =========================
     * 1️⃣ LỌC THEO DANH MỤC (CHA → CON)
     * ========================= */
        if ($request->filled('category_id')) {
            $categoryId = (int) $request->category_id;
            $childIds = Categories::getAllChildIds($categoryId);

            $allCategoryIds = array_merge([$categoryId], $childIds);
            $query->whereHas('categories', function ($q) use ($allCategoryIds) {
                $q->whereIn('categories.category_id', $allCategoryIds);
            });
        }

        // 2️⃣ LỌC THEO THƯƠNG HIỆU
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where('name', 'LIKE', "%{$keyword}%");
        }
        switch ($request->get('filter')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            case 'latest':
                $query->orderBy('created_at', 'desc')->limit(10);
                break;

            default: // newest
                $query->orderBy('created_at', 'desc');
                break;
        }
        $products = $query->paginate(24)->withQueryString();

        return response()->json([
            'status'  => true,
            'message' => 'Lấy danh sách sản phẩm thành công',
            'data'    => $products
        ]);
    }

    public function show($id)
    {

        return Product::with(['variant', 'image', 'categories', 'brand'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        // 1. Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'brand_id' => 'nullable|integer|exists:brands,brand_id',

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
                'brand_id' => $request->brand_id,
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
        $related = Product::whereHas('categories', function ($query) use ($product) {
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
    public function update(Request $request, $id)
    {
        // 1. Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'brand_id' => 'nullable|integer|exists:brands,brand_id',

            // Variants JSON string bắt buộc phải có
            'variants' => 'required|json',

            // Ảnh mới (new_variant_images) là optional khi update
            'new_variant_images' => 'nullable|array',
            'new_variant_images.*' => 'nullable|array',
            'new_variant_images.*.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5000',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // 2. Cập nhật thông tin cơ bản
            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
            ]);

            // 3. Xử lý Variants & Images
            $variantsData = json_decode($request->variants, true);
            // Lưu ý: Key ở đây phải khớp với key frontend gửi lên (Vue code trước gửi: new_variant_images)
            $newVariantImageGroups = $request->file('new_variant_images', []);

            // Duyệt qua từng nhóm biến thể (Từng màu)
            foreach ($variantsData as $index => $groupData) {

                // --- A. XỬ LÝ XÓA (Delete) ---

                // Xóa các Size đã bị user xóa
                if (!empty($groupData['deletedSizeIds'])) {
                    // Xóa trong DB bảng variants
                    $product->variant()->whereIn('variant_id', $groupData['deletedSizeIds'])->delete();
                    // Tùy chọn: Xóa luôn image liên kết với variant_id này nếu DB không set cascade
                    $product->image()->whereIn('variant_id', $groupData['deletedSizeIds'])->delete();
                }

                // Xóa các Ảnh đã bị user xóa (Ảnh cũ)
                if (!empty($groupData['deletedImageIds'])) {
                    $imagesToDelete = $product->image()->whereIn('image_id', $groupData['deletedImageIds'])->get();
                    foreach ($imagesToDelete as $img) {
                        // Xóa file vật lý (Kiểm tra xem ảnh có đang được dùng bởi variant khác không nếu cần thiết)
                        // Ở logic này mỗi dòng image là riêng biệt nên xóa thẳng
                        if (\Storage::disk('public')->exists($img->image_url)) {
                            \Storage::disk('public')->delete($img->image_url);
                        }
                        $img->delete();
                    }
                }

                // --- B. XỬ LÝ SIZES (Update & Create) ---

                $currentGroupVariantIds = []; // Mảng chứa ID của các size ĐANG HOẠT ĐỘNG của màu này

                foreach ($groupData['sizes'] as $sizeItem) {
                    // Kiểm tra: Nếu có ID thì là Update, không có (hoặc null) thì là Create
                    if (isset($sizeItem['id']) && $sizeItem['id']) {
                        // Update Size cũ
                        $variant = $product->variant()->find($sizeItem['id']);
                        if ($variant) {
                            $variant->update([
                                'color' => $groupData['color'], // Cập nhật màu (phòng trường hợp user đổi màu)
                                'size'  => $sizeItem['size'],
                                'stock' => $sizeItem['stock'],
                                'price' => ($sizeItem['price'] && $sizeItem['price'] > 0) ? $sizeItem['price'] : $product->price,
                            ]);
                            $currentGroupVariantIds[] = $variant->variant_id;
                        }
                    } else {
                        // Create Size mới
                        $newVariant = $product->variant()->create([
                            'color' => $groupData['color'],
                            'size'  => $sizeItem['size'],
                            'stock' => $sizeItem['stock'],
                            'price' => ($sizeItem['price'] && $sizeItem['price'] > 0) ? $sizeItem['price'] : $product->price,
                        ]);
                        $currentGroupVariantIds[] = $newVariant->variant_id;

                        // TODO (Nâng cao): Nếu muốn size mới này tự động nhận các ảnh CŨ của nhóm màu,
                        // bạn cần query lấy ảnh của các variant anh em và duplicate record image cho variant mới này.
                    }
                }

                // --- C. XỬ LÝ ẢNH MỚI (Upload New Images) ---

                // Kiểm tra xem nhóm màu hiện tại ($index) có file mới gửi lên không
                if (isset($newVariantImageGroups[$index])) {
                    foreach ($newVariantImageGroups[$index] as $imgKey => $file) {

                        // Tạo tên file
                        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

                        // Lưu file
                        $path = $file->storeAs('images/products', $fileName, 'public');

                        // Gán ảnh này cho TẤT CẢ variants (sizes) đang hoạt động của nhóm màu này
                        foreach ($currentGroupVariantIds as $variantId) {
                            $product->image()->create([
                                'product_id' => $product->product_id,
                                'variant_id' => $variantId,
                                'image_url'  => $path,
                                'is_main'    => 0, // Ảnh thêm mới thường không set làm ảnh chính ngay, hoặc tùy logic frontend
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật sản phẩm thành công!',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage()); // Ghi log lỗi để debug

            return response()->json([
                'status' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        $product = Product::with(['variants.images'])->findOrFail($id);

        // 1. Xóa file cứng trong storage
        foreach ($product->variants as $variant) {
            foreach ($variant->images as $image) {
                if (\Storage::exists($image->path)) {
                    \Storage::delete($image->path);
                }
            }
        }

        // 2. Xóa bản ghi liên quan
        foreach ($product->variants as $variant) {
            $variant->images()->delete(); // xóa bảng product_images
        }
        $product->variants()->delete(); // xóa bảng product_variants

        // 3. Xóa sản phẩm
        $product->delete();

        return response()->json([
            'message' => 'Xóa sản phẩm thành công!',
            'status' => true
        ]);
    }
}
