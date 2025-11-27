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
        // 1. Validate cơ bản (không validate image như file tại đây)
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|integer|exists:categories,category_id',
            'image' => 'nullable',
        ], [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.unique' => 'Tên danh mục này đã tồn tại.',
            'parent_id.exists' => 'ID danh mục cha không hợp lệ.',
        ]);

        try {
            $imagePath = null; // Khởi tạo biến imagePath là null

            // Nếu frontend gửi file thực sự
            if ($request->hasFile('image')) {
                // Validate file image
                $request->validate([
                    'image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                ]);

                $imageFile = $request->file('image');
                $imageName = time() . '_' . Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $imageFile->getClientOriginalExtension();
                $imagePath = $imageFile->storeAs('images/categories', $imageName, 'public');
            }
            // Nếu frontend gửi base64 string (data URI)
            elseif ($request->filled('image') && is_string($request->image) && preg_match('/^data:image\/(\w+);base64,/', $request->image, $type)) {
                $data = substr($request->image, strpos($request->image, ',') + 1);
                $data = base64_decode($data);
                if ($data === false) {
                    throw new \Exception('Dữ liệu ảnh base64 không hợp lệ.');
                }
                $extension = strtolower($type[1]);
                if (!in_array($extension, ['jpg','jpeg','png','gif','svg','webp'])) {
                    throw new \Exception('Loại ảnh không được hỗ trợ.');
                }
                $imageName = time() . '_'. Str::random(6) . '.' . $extension;
                $imagePath = 'images/categories/' . $imageName;
                Storage::disk('public')->put($imagePath, $data);
            }
            // Nếu frontend gửi URL hoặc chuỗi khác => không lỗi, ta có thể lưu URL trực tiếp hoặc bỏ qua
            elseif ($request->filled('image') && is_string($request->image)) {
                // Tuỳ nhu cầu: lưu URL trực tiếp hoặc bỏ qua. Ở đây lưu trực tiếp nếu là URL
                if (filter_var($request->image, FILTER_VALIDATE_URL)) {
                    $imagePath = $request->image;
                } else {
                    // nếu không muốn lưu chuỗi lạ thì set null
                    $imagePath = null;
                }
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
    public function destroy($id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Danh mục không tồn tại.'
            ], 404);
        }

        // Kiểm tra nếu danh mục có danh mục con
        if ($category->children()->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể xóa danh mục vì nó có danh mục con.'
            ], 400);
        }

        // Xóa ảnh liên quan nếu có
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Danh mục đã được xóa thành công.'
        ]);
    }
}