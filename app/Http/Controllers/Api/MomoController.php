<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderSuccessMail;

class MomoController extends Controller
{
    public function createPayment(Request $request)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $user_id = $request->user()->user_id;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'          => $user_id,
                'address_id'       => $request->address_id,
                'note'             => $request->note ?? '',
                'total_amount'     => $request->total_amount,
                'shipping_fee'     => $request->shipping_fee,
                'payment_method'   => $request->payment_method ?? 'momo',
                'promotion_id'     => $request->promotion_id ?? null,
                'discount_amount'  => $request->discount_amount ?? 0,
                'final_amount'     => $request->total_amount + $request->shipping_fee - ($request->discount_amount ?? 0),
                'status'           => 'pending',
            ]);

            foreach ($request->products as $product) {
                $order->orderItem()->create([
                    'variant_id' => $product['variant_id'],
                    'quantity'   => $product['quantity'],
                    'price'      => $product['price'],
                ]);
            }
            DB::commit();

            // Momo Payment Logic
            $endpoint = env('MOMO_ENDPOINT', "https://test-payment.momo.vn/v2/gateway/api/create");
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');
            $notifyUrl = env('MOMO_NOTIFY_URL'); // IPN
            $returnUrl = env('MOMO_RETURN_URL');

            $orderId = $order->order_id . '_' . time(); // Unique order ID for Momo
            $amount = (string)$order->final_amount;
            $orderInfo = "Thanh toan don hang #" . $order->order_id;
            $requestId = time() . "";
            $requestType = "captureWallet";
            $extraData = "";

            // Signature
            $rawHash = "accessKey=" . $accessKey .
                "&amount=" . $amount .
                "&extraData=" . $extraData .
                "&ipnUrl=" . $notifyUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $returnUrl .
                "&requestId=" . $requestId .
                "&requestType=" . $requestType;

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "Test",
                'storeId' => "MomoTestStore",
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $returnUrl,
                'ipnUrl' => $notifyUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            ];

            // Send Request to Momo
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
            $result = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $jsonResult = json_decode($result, true);

            return response()->json([
                'message' => 'Tạo đơn hàng thành công',
                'order_id' => $order->order_id,
                'payment_url' => $jsonResult['payUrl'] ?? null,
                'momo_response' => $jsonResult
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi tạo đơn hàng Momo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function momoReturn(Request $request)
    {
        // Handle return from Momo (Redirect URL)
        // Usually verifies signature again
        // For simplicity here, we assume if success, we update order.
        // NOTE: In production, rely on IPN (notifyUrl) for status update, not returnUrl.

        // Example check
        if ($request->resultCode == 0) {
            // Success
            // Extract order_id from orderId (order_id_timestamp)
            $parts = explode('_', $request->orderId);
            $realOrderId = $parts[0];

            $order = Order::find($realOrderId);
            if ($order) {
                if ($order->status != 'paid') {
                    $order->update(['status' => 'paid']);

                    // Clear cart
                    $cart = Cart::where('user_id', $order->user_id)->first();
                    if ($cart) {
                        foreach ($order->orderItem as $item) {
                            CartItem::where('variant_id', $item->variant_id)->delete();
                        }
                    }
                    // Send mail
                    try {
                        Mail::to($request->user()->email)->send(new OrderSuccessMail($order));
                    } catch (\Exception $e) {
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Thanh toán Momo thành công',
                    'order' => $order
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Thanh toán thất bại hoặc bị hủy'
        ]);
    }
}
