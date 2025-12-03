<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Lấy review theo product
    public function getByProduct($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('status', 'approved')
            ->with(['user:user_id,name'])   // load user
            ->orderBy('review_id', 'DESC')
            ->get();

        return response()->json($reviews);
    }

    // Tạo review (chỉ 1 lần)
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'rating'     => 'required|integer|min:1|max:5',
            'content'    => 'nullable|string',
        ]);

        // Check user đã đánh giá chưa
        $exists = Review::where('user_id', Auth::id())
                        ->where('product_id', $request->product_id)
                        ->first();

        if ($exists) {
            return response()->json([
                'message' => 'Bạn đã đánh giá sản phẩm này rồi.'
            ], 400);
        }

        // Tạo mới
        $review = Review::create([
            'user_id'    => Auth::id(),
            'product_id' => $request->product_id,
            'rating'     => $request->rating,
            'content'    => $request->content,
        ]);

        return response()->json($review);
    }

    // User được phép sửa đánh giá
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền sửa'], 403);
        }

        $review->update([
            'rating'  => $request->rating,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Cập nhật thành công']);
    }
}
