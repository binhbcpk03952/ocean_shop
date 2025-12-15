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
            ->with(['user:user_id,name,email'])
            ->orderBy('review_id', 'DESC')
            ->get();

        return response()->json([
            'product_id' => $productId,
            'total_reviews' => $reviews->count(),
            'reviews' => $reviews
        ]);
    }

    // Gửi review
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'rating'     => 'required|integer|min:1|max:5',
            'content'    => 'nullable|string',
        ]);

        $exists = Review::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Bạn đã đánh giá sản phẩm này rồi.'
            ], 400);
        }

        $review = Review::create([
            'user_id'    => Auth::id(),
            'product_id' => $request->product_id,
            'rating'     => $request->rating,
            'content'    => $request->content,
            'status'     => 'approved',
        ]);

        return response()->json($review, 201);
    }

    // Cập nhật review
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $user_id = $request->user()->user_id;
        if ($review->user_id !== $user_id) {
            return response()->json(['message' => 'Không có quyền sửa'], 403);
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'content' => 'nullable|string',
        ]);

        $review->update($request->only('rating', 'content'));

        return response()->json(['message' => 'Cập nhật thành công']);
    }

    // Xóa review
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== Auth::id() && optional(Auth::user())->role !== 'admin') {
            return response()->json(['message' => 'Không có quyền xóa'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Xóa thành công']);
    }
}
