<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;
use Illuminate\Support\Carbon;

class PromotionController extends Controller
{
    // Lấy danh sách
    public function index()
    {
        // Sắp xếp mới nhất lên đầu
        $promotions = Promotion::orderBy('created_at', 'desc')->get();
        return response()->json($promotions);
    }

    // Tạo mới


    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:promotions,code',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
        ]);

        $validated['start_date'] = Carbon::parse($validated['start_date']);
        $validated['end_date']   = Carbon::parse($validated['end_date']);

        $promotion = Promotion::create($validated);

        return response()->json([
            'message' => 'Tạo mã giảm giá thành công',
            'data' => $promotion
        ], 201);
    }



    // Cập nhật

    public function update(Request $request, $id)
    {
        $promotion = Promotion::find($id);
        if (!$promotion) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

        $validated = $request->validate([
            'code' => 'required|unique:promotions,code,' . $id . ',promotion_id',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
        ]);

        $validated['start_date'] = Carbon::parse($validated['start_date']);
        $validated['end_date']   = Carbon::parse($validated['end_date']);

        $promotion->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data' => $promotion
        ]);
    }


    // Xóa
    public function destroy($id)
    {
        $promotion = Promotion::find($id);
        if ($promotion) {
            $promotion->delete();
            return response()->json(['message' => 'Đã xóa mã giảm giá']);
        }
        return response()->json(['message' => 'Lỗi khi xóa'], 500);
    }
}