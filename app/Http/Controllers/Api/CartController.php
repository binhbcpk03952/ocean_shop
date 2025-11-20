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
}
