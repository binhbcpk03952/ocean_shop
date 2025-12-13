<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderSuccessMail;
use App\Models\OrderCancellation;
use Carbon\Carbon;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->user_id;
        $orders = Order::with(['orderItem.variant.product', 'orderItem.variant.image'])->where('user_id', $user_id)->orderBy('order_id', 'DESC')->get();
        return response()->json($orders);
    }
    public function getAllOrders()
    {
        $orders = Order::with(['orderItem.variant.product', 'orderItem.variant.image', 'addresses', 'user'])->orderBy('order_id', 'DESC')->get();
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
            $mail = $request->user()->email;
            Mail::to($mail)->send(new OrderSuccessMail($order));
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

        $order = Order::with(['orderItem.variant.product',]) // load full chi tiết
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
    public function updateStatus(Request $request, $id)
    {
        // 1. Validate dữ liệu đầu vào (chỉ chấp nhận các trạng thái hợp lệ)
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,shipping,completed,cancelled,failed',
        ]);

        try {
            // 2. Tìm đơn hàng theo ID
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'message' => 'Không tìm thấy đơn hàng',
                ], 404);
            }

            // 3. Kiểm tra logic (Tuỳ chọn: Không cho phép sửa nếu đơn đã hoàn thành hoặc đã huỷ)
            if ($order->status == 'completed' || $order->status == 'cancelled') {
                return response()->json([
                    'message' => 'Không thể cập nhật trạng thái cho đơn hàng đã hoàn tất hoặc đã hủy.',
                ], 400);
            }

            // 4. Cập nhật trạng thái
            $old_status = $order->status; // Lưu trạng thái cũ để log nếu cần
            $order->status = $request->status;
            $order->save();

            // 5. Trả về kết quả
            return response()->json([
                'message'    => 'Cập nhật trạng thái đơn hàng thành công',
                'order_id'   => $order->order_id,
                'old_status' => $old_status,
                'new_status' => $order->status,
                'updated_at' => $order->updated_at
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi cập nhật trạng thái',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function cancelOrder(Request $request, $id)
    {
        $user_id = $request->user()->user_id;

        // Tìm đơn hàng của đúng user đó
        $order = Order::where('order_id', $id)->where('user_id', $user_id)->first();

        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn'], 404);
        }

        // Chỉ cho phép huỷ nếu đơn đang ở trạng thái 'pending'
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Đơn hàng đã được xử lý, không thể hủy lúc này'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'message' => 'Đã hủy đơn hàng thành công',
            'order' => $order
        ], 200);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        // 1️⃣ Tìm đơn hàng
        $order = Order::where('order_id', $id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        // 2️⃣ Kiểm tra trạng thái có được hủy không
        $allowCancelStatus = ['pending', 'paid'];

        if (!in_array($order->status, $allowCancelStatus)) {
            return response()->json([
                'message' => 'Đơn hàng không thể hủy ở trạng thái hiện tại'
            ], 400);
        }

        // 3️⃣ Validate dữ liệu
        $request->validate([
            'reason_code' => 'required|string|max:50',
            'reason_text' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // 4️⃣ Lưu thông tin hủy đơn
            OrderCancellation::create([
                'order_id'      => $order->order_id,
                'reason_code'   => $request->reason_code,
                'reason_text'   => $request->reason_text,
                'canceled_by'   => 'user',
                'refund_status' => $order->status === 'paid'
                    ? 'pending'
                    : 'none',
                'refund_amount' => $order->status === 'paid'
                    ? $order->total_amount
                    : 0,
                'canceled_at'   => Carbon::now(),
            ]);

            // 5️⃣ Update trạng thái đơn
            $order->update([
                'status' => 'cancelled'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Hủy đơn hàng thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Hủy đơn hàng thất bại',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
