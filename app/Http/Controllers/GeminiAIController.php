<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class GeminiAIController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string',
            ]);

            $question = strtolower(trim($request->message));
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                return response()->json(["error" => "Missing GEMINI_API_KEY"], 500);
            }

            /* -----------------------------------------
             * 1. LỌC SẢN PHẨM
             ----------------------------------------- */
            $products = Product::query();
            $filtered = false;

            /* ========= SIZE & GIỚI TÍNH ========= */
            $sizeSuggest = null;
            $height = null;
            $genderFilter = null;

            // ─── PHÂN TÍCH CÂN NẶNG ───
            if (preg_match('/(\d+)\s*(kg|ký|kilô)/ui', $question, $m)) {
                $weight = intval($m[1]);

                if ($weight < 50) $sizeSuggest = "S";
                elseif ($weight < 60) $sizeSuggest = "M";
                elseif ($weight < 70) $sizeSuggest = "L";
                elseif ($weight < 80) $sizeSuggest = "XL";
                else $sizeSuggest = "XXL";

                $filtered = true;
            }

            // ─── PHÂN TÍCH CHIỀU CAO ───
            if (preg_match('/(\d+(?:[\.,]\d+)?)\s*(m|met|cm)/ui', $question, $m)) {

                $value = floatval(str_replace(',', '.', $m[1]));

                if ($m[2] == "cm") {
                    $height = $value / 100;
                } else {
                    $height = $value;
                }
            }

            // ─── ĐIỀU CHỈNH SIZE THEO CHIỀU CAO ───
            if ($sizeSuggest && $height) {
                if ($height < 1.60 && $sizeSuggest == "L") $sizeSuggest = "M";
                if ($height < 1.60 && $sizeSuggest == "XL") $sizeSuggest = "L";

                if ($height > 1.78 && $sizeSuggest == "L") $sizeSuggest = "XL";
                if ($height > 1.78 && $sizeSuggest == "XL") $sizeSuggest = "XXL";
            }

            /* ========= LỌC GIỚI TÍNH THEO CATEGORY ========= */

            $isMale   = preg_match('/(nam|đàn ông|men|male|boy)/ui', $question);
            $isFemale = preg_match('/(nữ|phụ nữ|women|female|girl)/ui', $question);
            $isKid    = preg_match('/(trẻ em|kid|children|bé|em bé)/ui', $question);

            $rootCategoryId = null;

            if ($isMale)  $rootCategoryId = 7;
            if ($isFemale) $rootCategoryId = 8;
            if ($isKid)   $rootCategoryId = 9;

            if ($rootCategoryId) {

                // lấy toàn bộ category con có cùng parent
                $categoryIds = DB::table('categories')
                    ->where('category_id', $rootCategoryId)
                    ->orWhere('parent_id', $rootCategoryId)
                    ->pluck('category_id')
                    ->toArray();

                $products->whereIn('category_id', $categoryIds);
                $filtered = true;

                if ($isMale)  $genderFilter = 'male';
                if ($isFemale) $genderFilter = 'female';
                if ($isKid)   $genderFilter = 'kid';
            }

            /* ========= LỌC KHOẢNG GIÁ LINH HOẠT ========= */
            $regexPriceRange = '/(\d+(?:[\.,]\d+)*)(\s*(k|nghìn|ngàn|tr|triệu|m)?)\s*(?:-|đến|tới|->|=>)\s*(\d+(?:[\.,]\d+)*)(\s*(k|nghìn|ngàn|tr|triệu|m)?)/ui';

            if (preg_match($regexPriceRange, $question, $m)) {

                $rawMin = str_replace('.', '', $m[1]);
                $rawMax = str_replace('.', '', $m[4]);

                $min = floatval(str_replace(',', '.', $rawMin));
                $max = floatval(str_replace(',', '.', $rawMax));

                $unitMin = strtolower(trim($m[3] ?? ''));
                $unitMax = strtolower(trim($m[6] ?? ''));

                if (in_array($unitMin, ['k','nghìn','ngàn']))  $min *= 1000;
                if (in_array($unitMin, ['tr','triệu','m']))   $min *= 1000000;

                if (in_array($unitMax, ['k','nghìn','ngàn']))  $max *= 1000;
                if (in_array($unitMax, ['tr','triệu','m']))   $max *= 1000000;

                $products->whereBetween('price', [$min, $max]);
                $filtered = true;
            }

            /* ========= LỌC DƯỚI DẺ ========= */
            if (preg_match('/dưới\s*(\d+(?:[\.,]\d+)*)(\s*(k|nghìn|ngàn|tr|triệu|m)?)/ui', $question, $m)) {

                $num = floatval(str_replace(',', '.', str_replace('.', '', $m[1])));
                $unit = strtolower(trim($m[3] ?? ''));

                if (in_array($unit, ['k','nghìn','ngàn'])) $num *= 1000;
                if (in_array($unit, ['tr','triệu','m']))    $num *= 1000000;

                $products->where('price', '<=', $num);
                $filtered = true;
            }

            /* ========= LỌC TRÊN GIÁ ========= */
            if (preg_match('/trên\s*(\d+(?:[\.,]\d+)*)(\s*(k|nghìn|ngàn|tr|triệu|m)?)/ui', $question, $m)) {

                $num = floatval(str_replace(',', '.', str_replace('.', '', $m[1])));
                $unit = strtolower(trim($m[3] ?? ''));

                if (in_array($unit, ['k','nghìn','ngàn'])) $num *= 1000;
                if (in_array($unit, ['tr','triệu','m']))    $num *= 1000000;

                $products->where('price', '>=', $num);
                $filtered = true;
            }

            /* ========= LỌC KEYWORD TÊN SẢN PHẨM ========= */
            if (preg_match('/(vợt|áo|gile|yonex|nike|quần|giày|khoác)/ui', $question, $m)) {
                $products->where('name', 'LIKE', "%{$m[1]}%");
                $filtered = true;
            }

            /* ========= HỎI SỐ LƯỢNG SẢN PHẨM ========= */
            if (preg_match('/(bao nhiêu|mấy|số lượng).*sản phẩm/ui', $question)) {
                $count = Product::count();
                return response()->json([
                    "reply" => "Chào bạn! Hiện tại shop đang có tổng cộng $count sản phẩm.",
                    "products" => []
                ]);
            }

            /* KẾT QUẢ LỌC */
            $foundProducts = $filtered ? $products->get() : Product::all();

            /* Nếu không có sản phẩm phù hợp */
            if ($foundProducts->count() === 0) {
                return response()->json([
                    "reply" => "Xin lỗi bạn. Shop hiện chưa có sản phẩm nào phù hợp với yêu cầu: {$question}.
Bạn có thể thử đổi khoảng giá, giới tính hoặc loại sản phẩm nhé!",
                    "products" => []
                ]);
            }

            /* -----------------------------------------
             * 2. TẠO CONTEXT GỬI GEMINI
             ----------------------------------------- */
            $context =
"Bạn là trợ lý AI hỗ trợ tư vấn sản phẩm cho khách.
Không bao giờ nói rằng khách hàng cung cấp dữ liệu.
KHÔNG sử dụng ký tự đặc biệt như *, _, ~, # hoặc Markdown.
Chỉ nói 'dựa trên dữ liệu sản phẩm của shop chúng tôi'.
Trả lời gọn, thân thiện, chuyên nghiệp như nhân viên tư vấn.

Danh sách sản phẩm phù hợp:\n\n";

            foreach ($foundProducts as $p) {
                $context .= "- ID: {$p->product_id}\nTên: {$p->name}\nGiá: {$p->price} VNĐ\n\n";
            }

            if ($sizeSuggest) {
                $context .= "Gợi ý size phù hợp: $sizeSuggest.\n\n";
            }

            if ($genderFilter == 'male') $context .= "Khách đang tìm sản phẩm theo nhóm: Nam.\n\n";
            if ($genderFilter == 'female') $context .= "Khách đang tìm sản phẩm theo nhóm: Nữ.\n\n";
            if ($genderFilter == 'kid') $context .= "Khách đang tìm sản phẩm theo nhóm: Trẻ em.\n\n";

            $finalPrompt = $context .
"Hãy tư vấn sản phẩm phù hợp, dựa trên dữ liệu sản phẩm của shop chúng tôi.
Câu hỏi của khách: {$question}";


            /* -----------------------------------------
             * 3. GỌI GEMINI
             ----------------------------------------- */
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
                ?? "Xin lỗi, tôi chưa thể phản hồi lúc này.";

            /* -----------------------------------------
             * RETURN
             ----------------------------------------- */
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
                "error" => $e->getMessage(),
                "line" => $e->getLine(),
            ], 500);
        }
    }
}
