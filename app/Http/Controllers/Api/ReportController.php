<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
// Lưu ý: Import đúng Model của bạn

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Lấy năm từ request, mặc định là năm hiện tại
        $year = $request->input('year', date('Y'));

        // ====================================================
        // PHẦN 1: DOANH THU THEO THÁNG (Line Chart)
        // ====================================================
        $monthlyRevenue = Order::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(final_amount) as total_revenue')
            )
            ->whereYear('created_at', $year)
            ->where('status', 'completed') // CHÚ Ý: Chỉ lấy đơn hàng đã hoàn thành/đã thanh toán
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Xử lý lấp đầy các tháng không có doanh thu (để biểu đồ không bị gãy)
        // Ví dụ: Tháng 1 có, Tháng 2 không, Tháng 3 có -> Cần tạo Tháng 2 = 0
        $filledMonthlyRevenue = collect(range(1, 12))->map(function ($month) use ($monthlyRevenue) {
            $found = $monthlyRevenue->firstWhere('month', $month);
            return [
                'month' => $month,
                'total_revenue' => $found ? $found->total_revenue : 0
            ];
        });

        // ====================================================
        // PHẦN 2: DOANH THU THEO PT THANH TOÁN (Pie Chart)
        // ====================================================
        $paymentRevenue = Order::select(
                'payment_method',
                DB::raw('SUM(final_amount) as total_revenue')
            )
            ->whereYear('created_at', $year)
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();

        // ====================================================
        // PHẦN 3: TOP SẢN PHẨM BÁN CHẠY (Bar Chart)
        // ====================================================
        // Cần join bảng order_items với orders (để lọc theo năm) và products (lấy tên)
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_item_id')
             ->join('product_variants', 'product_variants.variant_id', '=', 'order_items.variant_id')
            ->join('products', 'products.product_id', '=', 'product_variants.product_id')
            ->select(
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->whereYear('orders.created_at', $year)
            ->where('orders.status', 'completed')
            ->groupBy('products.product_id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5) // Lấy top 5
            ->get();

        // ====================================================
        // TRẢ VỀ JSON
        // ====================================================
        return response()->json([
            'monthlyRevenue' => $filledMonthlyRevenue,
            'paymentRevenue' => $paymentRevenue,
            'topProducts' => $topProducts
        ]);
    }
}
