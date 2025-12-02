<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class GeminiAIController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string',
            ]);

            $question = strtolower($request->message);
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                return response()->json(["error" => "Missing GEMINI_API_KEY"], 500);
            }

            /* 1. XỬ LÝ LỌC SẢN PHẨM */
            $products = Product::query();
            $filtered = false;

            // Chuẩn hóa dấu chấm, khoảng trắng
            $normalized = str_replace(['.', ' '], '', $question);

            /* Lọc dưới giá */
            if (preg_match('/dưới\s*(\d+)(k|nghìn)?/ui', $normalized, $m)) {
                $num = intval($m[1]);
                if (!empty($m[2])) $num *= 1000;
                $products->where('price', '<=', $num);
                $filtered = true;
            }

            /* Lọc trên giá */
            if (preg_match('/trên\s*(\d+)(k|nghìn)?/ui', $normalized, $m)) {
                $num = intval($m[1]);
                if (!empty($m[2])) $num *= 1000;
                $products->where('price', '>=', $num);
                $filtered = true;
            }

            /* Lọc trong khoảng giá (100k - 300k / 100k đến 300k) */
            if (preg_match('/(\d+)(k)?\s*[-đến]+\s*(\d+)(k)?/ui', $normalized, $m)) {
                $min = intval($m[1]);
                $max = intval($m[3]);
                if (!empty($m[2])) $min *= 1000;
                if (!empty($m[4])) $max *= 1000;

                $products->whereBetween('price', [$min, $max]);
                $filtered = true;
            }

            /* Lọc theo tên sản phẩm (nếu không hỏi giá) */
            if (!$filtered && preg_match('/(vợt|áo|gile|yonex|nike|quần|giày|khoác)/ui', $question, $m)) {
                $products->where('name', 'LIKE', "%" . $m[1] . "%");
                $filtered = true;
            }

            /* Nếu không lọc → trả toàn bộ sản phẩm */
            $foundProducts = $filtered ? $products->get() : Product::all();

            /* 2. TẠO PROMPT GỬI GEMINI */

            $context = 
"Bạn là trợ lý AI giới thiệu sản phẩm cho khách.
❗ Không bao giờ nói rằng khách hàng cung cấp dữ liệu.
❗ Luôn xưng: 'shop chúng tôi', 'danh sách sản phẩm của shop chúng tôi'.
❗ Trả lời ngắn gọn, thân thiện như một nhân viên bán hàng.

Dữ liệu sản phẩm hiện có của shop:\n\n";

            if ($foundProducts->count() === 0) {
                $context .= "- Không có sản phẩm nào phù hợp.\n\n";
            } else {
                foreach ($foundProducts as $p) {
                    $context .= "- ID: {$p->product_id}\nTên: {$p->name}\nGiá: {$p->price} VNĐ\n\n";
                }
            }

            $finalPrompt =
$context . 
"Hãy trả lời tự nhiên, rõ ràng, thân thiện dựa trên dữ liệu sản phẩm của shop chúng tôi. 
Câu hỏi của khách: {$question}";


            /* 3. GỌI GEMINI API */

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey";

            $response = Http::post($url, [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $finalPrompt]
                        ]
                    ]
                ]
            ]);

            $data = $response->json();

            $reply = $data['candidates'][0]['content']['parts'][0]['text']
                ?? "Xin lỗi, hiện tại tôi chưa thể trả lời.";


            /* 4. TRẢ VỀ FRONTEND */

            return response()->json([
                "reply" => $reply,
                "products" => $foundProducts->map(fn ($p) => [
                    "id" => $p->product_id,
                    "name" => $p->name,
                    "price" => $p->price
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "error" => "Server Error",
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }
}
