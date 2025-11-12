<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Đăng ký thành công'], 201);
    }

    public function login(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Tìm người dùng bằng email
        $user = User::where('email', $request->email)->first();

        // 3. Kiểm tra người dùng và mật khẩu
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không hợp lệ.'
            ], 401);
        }

        // 4. Xóa các token cũ và tạo token mới (Sanctum)
        // LƯU Ý: Nếu bạn đang sử dụng SPA, bạn có thể muốn sử dụng `createToken` với tên thích hợp.
        // Dùng `sanctum:generate-token` nếu bạn dùng Flutter/React Native.
        // Với Vue.js (SPA), chúng ta thường dùng `createToken`.
        $user->tokens()->where('name', 'auth_token')->delete(); // Xóa token cũ cùng tên
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Trả về token và thông tin người dùng
        return response()->json([
            'message' => 'Đăng nhập thành công!',
            'token' => $token,
            'user' => $user, // Thông tin người dùng sẽ được trả về
        ]);
    }

    /**
     * @OA\Post(
     * path="/logout",
     * summary="Đăng xuất người dùng (thu hồi token)",
     * tags={"Authentication"},
     * security={{"sanctum":{}}},
     * @OA\Response(
     * response=200,
     * description="Đăng xuất thành công",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Đăng xuất thành công!")
     * )
     * )
     * )
     */
    public function logout(Request $request)
    {
        // 1. Thu hồi (xóa) token hiện tại của người dùng đã xác thực
        // Điều này chỉ hoạt động khi yêu cầu được gửi kèm theo token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đăng xuất thành công!'
        ]);
    }

    /**
     * @OA\Get(
     * path="/user",
     * summary="Lấy thông tin người dùng hiện tại",
     * tags={"Authentication"},
     * security={{"sanctum":{}}},
     * @OA\Response(
     * response=200,
     * description="Thông tin người dùng",
     * @OA\JsonContent(ref="#/components/schemas/User")
     * )
     * )
     */
    public function user(Request $request)
    {
        // Trả về thông tin người dùng đã được xác thực (Auth::user())
        return response()->json($request->user());
    }
}