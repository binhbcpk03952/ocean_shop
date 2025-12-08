<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Lấy ID của người dùng đã được xác thực thông qua request
        $user_id = $request->user()->user_id;
        $cart = Cart::with(['cartItem.variant.product', 'cartItem.variant.image'])->where('user_id', $user_id)->first();
        return response()->json($cart);
    }

    public function store(Request $request)
    {
        // 1. Validate dữ liệu gửi lên
        $request->validate([
            'variant_id' => 'required|exists:product_variants,variant_id',
            'count' => 'required|integer|min:1',
        ]);

        // 2. Lấy user_id của người dùng đã đăng nhập
        $user_id = $request->user()->user_id;


        // 3. Tìm hoặc tạo mới giỏ hàng cho người dùng
        // firstOrCreate sẽ tìm bản ghi có user_id, nếu không thấy sẽ tạo mới
        $cart = Cart::firstOrCreate(['user_id' => $user_id]);

        // 4. Tìm hoặc tạo mới sản phẩm trong giỏ hàng (CartItem)
        // updateOrCreate sẽ tìm CartItem với cart_id và variant_id
        $cartItem = CartItem::where('cart_id', $cart->cart_id)
            ->where('variant_id', $request->variant_id)
            ->first();

        if ($cartItem) {
            // Nếu sản phẩm đã tồn tại, cộng dồn số lượng
            $cartItem->quantity += $request->count;
            $cartItem->save();
        } else {
            // Nếu chưa tồn tại, tạo mới CartItem
            $cart->cartItem()->create([
                'variant_id' => $request->variant_id,
                'quantity' => $request->count,
            ]);
        }

        // 5. Trả về thông báo thành công
        return response()->json(['message' => 'Sản phẩm đã được thêm vào giỏ hàng thành công!'], 200);
    }
    public function updateQuantity(Request $request, $id)
    {
        // 1. Validate dữ liệu
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // 2. Tìm CartItem theo ID
        $cartItem = CartItem::find($id);

        if (!$cartItem) {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ hàng'], 404);
        }

        // 3. Kiểm tra quyền sở hữu (CartItem thuộc về user hiện tại)
        $user_id = $request->user()->user_id;
        $cart = Cart::find($cartItem->cart_id);

        if ($cart->user_id !== $user_id) {
            return response()->json(['message' => 'Không có quyền cập nhật giỏ hàng này'], 403);
        }

        // 4. Cập nhật số lượng
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'message' => 'Cập nhật số lượng thành công!',
            'status' => true,
            'cartItem' => $cartItem
        ], 200);
    }
    public function updateVariant(Request $request, $cart_item_id)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,variant_id',
        ]);
        $cartItem = CartItem::find($cart_item_id);

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm không tồn tại trong giỏ hàng'
            ], 404);
        }

        $user_id = $request->user()->user_id;

        $cart = Cart::find($cartItem->cart_id);

        if (!$cart || $cart->user_id !== $user_id) {
            return response()->json([
                'status' => false,
                'message' => 'Không có quyền cập nhật giỏ hàng này'
            ], 403);
        }

        $newVariantId = $request->variant_id;

        // Kiểm tra xem biến thể mới đã tồn tại trong giỏ chưa
        $existingItem = CartItem::where('cart_id', $cartItem->cart_id)
            ->where('variant_id', $newVariantId)
            ->where('cart_item_id', '!=', $cart_item_id)
            ->first();

        if ($existingItem) {
            // ✅ Nếu đã tồn tại → cộng dồn số lượng
            $existingItem->quantity += $cartItem->quantity;
            $existingItem->save();

            // ✅ Xoá cart item cũ
            $cartItem->delete();

            return response()->json([
                'status' => true,
                'message' => 'Đã gộp số lượng vào sản phẩm đã có trong giỏ',
                'cart_item' => $existingItem
            ]);
        }

        // ✅ Nếu chưa tồn tại → chỉ đổi variant_id bình thường
        $cartItem->variant_id = $newVariantId;
        $cartItem->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật biến thể thành công',
            'cart_item' => $cartItem
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user_id = $request->user()->user_id;
        if (!$user_id) {
            return response()->json(['message' => 'Không có quyền cập nhật giỏ hàng này'], 403);
        }
        $cart_items = CartItem::find($id);
        if (!$cart_items) {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ hàng'], 404);
        }

        $cart_items->delete();
        return response()->json(['status' => 'Deleted', 'status' => true]);
    }
}
