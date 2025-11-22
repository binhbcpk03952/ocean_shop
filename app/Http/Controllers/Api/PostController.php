<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Post; // Import Model Post
use Illuminate\Support\Facades\Storage; // Để xử lý file
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        // 1. Xác thực dữ liệu
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts,slug|max:255',
            'content' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Ảnh max 2MB
        ]);
        $user_id = $request->user()->user_id;

        $thumbnailPath = null;

        // 2. Xử lý Upload Thumbnail
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            // Tạo tên file duy nhất
            $imageName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $thumbnailPath = $file->storeAs('images/posts', $imageName, 'public');
        }

        // 3. Lưu dữ liệu vào Database
        $post = Post::create([
            'user_id' => $user_id,
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'thumbnail_path' => $thumbnailPath,
        ]);

        // 4. Trả về phản hồi thành công
        return response()->json([
            'message' => 'Bài viết đã được đăng thành công!',
            'post' => $post,
        ], 201); // 201 Created
    }
    public function index(Request $request)
    {
        $posts = Post::all();
        return response()->json($posts);
    }
    public function show($id)
    {
        return Post::findOrFail($id);
    }
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json([
            'message' => 'Bài viết đã được xóa thành công!',
        ]);
    }
}
