<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Categories::all()->toArray(); // Chuyển sang mảng
        $categories = $this->buildTree($categories); // Gọi phương thức nội bộ
        return response()->json($categories);
    }

    private function buildTree(array $elements, $parentId = 0)
    {
        $branch = [];
        foreach ($elements as $element) {
            $idKey = array_key_exists('category_id', $element) ? 'category_id' : 'id';

            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element[$idKey]);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
    public function store(Request $request)
    {
        // 1. Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|integer|exists:categories,category_id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.unique' => 'Tên danh mục này đã tồn tại.',
            'parent_id.exists' => 'ID danh mục cha không hợp lệ.',
        ]);

        try {
            $imagePath = null; // Khởi tạo biến imagePath là null

            // 2. Xử lý lưu trữ File ảnh
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                // Dùng time() và tên file gốc (hoặc tên đã làm sạch) để tạo tên file duy nhất
                $imageName = time() . '_' . Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $imageFile->getClientOriginalExtension();

                $imagePath = $imageFile->storeAs('images/categories', $imageName, 'public');
            }

            // 3. Lấy parent_id
            $parentId = $request->parent_id;

            // 4. Lưu thông tin danh mục vào database
            $category = Categories::create([
                'name' => $request->name,
                'parent_id' => $parentId, // Lưu ID danh mục cha
                'image' => $imagePath,
            ]);

            // 5. Trả về phản hồi thành công
            return response()->json([
                'success' => true,
                'message' => 'Danh mục đã được thêm thành công!',
                'category' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình thêm danh mục.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
