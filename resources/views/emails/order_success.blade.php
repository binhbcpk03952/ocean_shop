<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng</title>
    <style>
        /* Reset CSS cơ bản cho Email */
        body { margin: 0; padding: 0; background-color: #f4f6f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }

        /* Màu thương hiệu */
        .brand-color { color: #3497e0; }
        .bg-brand { background-color: #3497e0; }

        /* Responsive cơ bản */
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content-padding { padding: 20px !important; }
        }
    </style>
</head>
<body style="background-color: #f4f6f8; margin: 0; padding: 20px 0;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">

                <table border="0" cellpadding="0" cellspacing="0" width="600" class="container" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">

                    <tr>
                        <td align="center" style="padding: 30px 0; background-color: #ffffff; border-bottom: 1px solid #eeeeee;">
                            <h2 style="color: #3497e0; margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px;">
                                OCEAN SHOP
                            </h2>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background-color: #3497e0; padding: 40px 20px;">
                            <div style="width: 60px; height: 60px; background-color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; line-height: 60px; font-size: 30px; color: #3497e0;">
                                ✓
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;">Thanh toán thành công!</h1>
                            <p style="color: #e6f4ff; margin: 10px 0 0; font-size: 16px;">Cảm ơn bạn đã mua sắm tại Ocean Shop</p>
                        </td>
                    </tr>

                    <tr>
                        <td class="content-padding" style="padding: 30px 40px;">
                            <p style="color: #555555; font-size: 16px; margin-bottom: 20px;">
                                Xin chào <strong>{{ $order->user->name ?? 'Khách hàng' }}</strong>,
                            </p>
                            <p style="color: #555555; font-size: 15px; line-height: 1.6;">
                                Đơn hàng <strong>#{{ $order->order_id }}</strong> của bạn đã được thanh toán thành công. Chúng tôi đang tiến hành đóng gói và sẽ bàn giao cho đơn vị vận chuyển sớm nhất.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td class="content-padding" style="padding: 0 40px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f9f9f9; border-radius: 6px; border: 1px dashed #e0e0e0;">
                                <tr>
                                    <td style="padding: 20px;">

                                        <table width="100%" cellspacing="0">
                                            <tr>
                                                <td style="padding-bottom: 10px; color: #666; font-size: 14px;">Tạm tính</td>
                                                <td align="right" style="padding-bottom: 10px; color: #333; font-size: 14px; font-weight: bold;">
                                                    {{ number_format($order->final_amount + $order->discount_amount - $order->shipping_fee) }} VNĐ
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="padding-bottom: 10px; color: #666; font-size: 14px;">Phí vận chuyển</td>
                                                <td align="right" style="padding-bottom: 10px; color: #333; font-size: 14px;">
                                                    {{ number_format($order->shipping_fee) }} VNĐ
                                                </td>
                                            </tr>

                                            @if($order->discount_amount > 0)
                                            <tr>
                                                <td style="padding-bottom: 10px; color: #666; font-size: 14px;">Giảm giá</td>
                                                <td align="right" style="padding-bottom: 10px; color: #2ecc71; font-size: 14px;">
                                                    -{{ number_format($order->discount_amount) }} VNĐ
                                                </td>
                                            </tr>
                                            @endif

                                            <tr>
                                                <td colspan="2" style="border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px;"></td>
                                            </tr>

                                            <tr>
                                                <td style="padding-top: 15px; color: #333; font-size: 16px; font-weight: bold;">Tổng thanh toán</td>
                                                <td align="right" style="padding-top: 15px; color: #3497e0; font-size: 20px; font-weight: bold;">
                                                    {{ number_format($order->final_amount) }} VNĐ
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding-bottom: 40px;">
                            <a href="{{ url('/account/orders/'.$order->order_id) }}" style="background-color: #3497e0; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 16px; display: inline-block;">
                                Xem chi tiết đơn hàng
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background-color: #f4f6f8; padding: 20px; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                Đây là email tự động, vui lòng không trả lời email này.<br>
                                © 2025 <strong>Ocean Shop</strong>. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
                </td>
        </tr>
    </table>

</body>
</html>
