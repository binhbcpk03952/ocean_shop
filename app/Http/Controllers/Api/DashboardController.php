<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stockOrder = Order::all()->count();
        $stockUser =  User::all()->where('role', 'user')->count();
        //    $totalProductBestSelling = OrderItem::with(['variant.product'])->where('produ')
        $threshold = 3; // mức tối thiểu để coi là "bán chạy"

        $bestSellingProducts = DB::table('order_items')
            ->join('product_variants', 'order_items.variant_id', '=', 'product_variants.variant_id')
            ->join('products', 'product_variants.product_id', '=', 'products.product_id')
            ->select('products.product_id', 'products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.product_id', 'products.name')
            ->havingRaw('SUM(order_items.quantity) > ?', [$threshold])
            ->orderByDesc('total_sold')
            ->get();

        return response()->json([
            'totalOrder' => $stockOrder,
            'totalUser' => $stockUser,
            'totalProduct' => $bestSellingProducts,
        ]);
    }
}
