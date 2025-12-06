<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->user_id;
        $orders = Order::with(['orderItem.variant.product', 'orderItem.variant.image'])->where('user_id', $user_id)->first();
        return response()->json($orders);
    }

    public function store(Request $request)
    {

        $request->validate([
            'address_id'      => 'required|integer',
            'total_amount'    => 'required|integer',
            'shipping_fee'    => 'required|integer',
            'payment_method' => 'required|string',
            'discount_amount' => 'nullable|integer',
            'promotion_id'   => 'nullable|integer',
            'note'            => 'nullable|string',
            'products'        => 'required|array',
            'products.*.cart_item_id' => 'required|integer',
        ]);

        $user_id = $request->user()->user_id;
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'          => $user_id,
                'address_id'       => $request->address_id,
                'note'             => $request->note ?? '',
                'total_amount'     => $request->total_amount,
                'shipping_fee'     => $request->shipping_fee,       // ✅ sửa lỗi shpping_fee
                'payment_method'  => $request->payment_method,
                'promotion_id'    => $request->promotion_id ?? null,
                'discount_amount' => $request->discount_amount ?? 0,
                'final_amount'    => $request->total_amount + $request->shipping_fee - ($request->discount_amount ?? 0),
                'status'           => 'pending',                     // ✅ thêm status tránh lỗi NOT NULL
            ]);

            foreach ($request->products as $product) {
                $order->orderItem()->create([           // ✅ đúng tên quan hệ
                    'variant_id' => $product['variant_id'],
                    'quantity'   => $product['quantity'],
                    'price'      => $product['price'],
                ]);
            }
            CartItem::where('cart_item_id', $product['cart_item_id'])->delete();


            DB::commit();
            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order'   => $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Lỗi khi đặt hàng',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function getLatestOrder(Request $request)
    {
        $user_id = $request->user()->user_id;

        $order = Order::with(['orderItem.variant.product', ]) // load full chi tiết
            ->where('user_id', $user_id)
            ->orderByDesc('order_id') // hoặc created_at
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Không tìm thấy đơn hàng nào'
            ], 404);
        }

        return response()->json([
            'message' => 'Lấy đơn hàng mới nhất thành công',
            'order'   => $order
        ], 200);
    }
}
