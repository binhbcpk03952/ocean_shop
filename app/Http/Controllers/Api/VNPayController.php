<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderSuccessMail;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = $request->user()->user_id;
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
        DB::commit();

        $vnp_TxnRef = $order->order_id;

        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = env('VNP_RETURN_URL');
        $vnp_OrderInfo = "Thanh toan don hang";
        $vnp_OrderType = "billpayment";

        // ✅ TÍNH TIỀN CHUẨN
        $totalAmount    = (float) ($request->total_amount ?? 0);
        $shippingFee    = (float) ($request->shipping_fee ?? 0);
        $discountAmount = (float) ($request->discount_amount ?? 0);

        $vnp_Amount = ($totalAmount + $shippingFee - $discountAmount) * 100;

        if ($vnp_Amount <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Số tiền thanh toán không hợp lệ'
            ], 400);
        }
        $vnp_Locale = "vn";
        $vnp_BankCode = "";
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        ];
        ksort($inputData);
        $query = "";
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            $hashdata .= ($hashdata ? '&' : '') . urlencode($key) . "=" . urlencode($value);
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return response()->json([
            'payment_url' => $vnp_Url
        ]);
    }

    public function vnpayReturn(Request $request)
    {
        $order = Order::with('orderItem')->find($request->vnp_TxnRef);

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($request->vnp_ResponseCode === '00') {

            DB::beginTransaction();

            try {
                // ✅ CẬP NHẬT TRẠNG THÁI ĐƠN
                $order->update(['status' => 'paid']);
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    foreach ($order->orderItem as $item) {
                        CartItem::where('variant_id', $item->variant_id)
                            ->delete();
                    }
                }
                DB::commit();
                $mail = $request->user()->email;
                Mail::to($mail)->send(new OrderSuccessMail($order));
                return response()->json([
                    'status' => true,
                    'message' => 'Thanh toán thành công',
                    'order' => $order
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Lỗi xử lý đơn hàng',
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            DB::beginTransaction();

            try {
                // ✅ XOÁ CHI TIẾT ĐƠN
                $order->orderItem()->delete();
                // ✅ XOÁ ĐƠN
                $order->delete();
                DB::commit();
                return response()->json([
                    'status' => false,
                    'message' => 'Thanh toán thất bại'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Lỗi khi huỷ đơn',
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
