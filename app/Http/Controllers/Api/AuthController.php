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
            'password' => 'required|min:6',
        ], [
            'email.unique' => 'Email đã được sử dụng.',
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
                'message' => 'Thông tin đăng nhập không hợp lệ.',
            ], 401);
        }

        if ($user->is_locked) {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ], 403);
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
    public function logout(Request $request)
    {
        // 1. Thu hồi (xóa) token hiện tại của người dùng đã xác thực
        // Điều này chỉ hoạt động khi yêu cầu được gửi kèm theo token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đăng xuất thành công!'
        ]);
    }
    public function user(Request $request)
    {
        // Trả về thông tin người dùng đã được xác thực (Auth::user())
        return response()->json($request->user());
    }

    public function users(Request $request)
    {
        $users = User::all();
        return response()->json($users);
    }
    public function update(Request $request, $user_id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user_id . ',user_id',
            'role' => 'required|in:admin,user',
            'is_locked' => 'nullable|boolean',
        ], [
            'email.unique' => 'Email đã được sử dụng.',
        ]);
        $user = User::findOrFail($user_id);
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại!'], 404);
        }
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_locked' => $request->has('is_locked') ? $request->is_locked : $user->is_locked,
        ]);

        return response()->json(['message' => 'Cập nhật thành công!', $user], 200);
    }
    public function destroy($user_id)
    {
        $user = User::findOrFail($user_id);
        $userLogin = Auth::user();
        if ($user->user_id === $userLogin->user_id) {
            return response()->json(['message' => 'Bạn không thể xóa chính mình!'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'Xóa người dùng thành công!', 'status' => true], 200);
    }


    public function changePassword(Request $request, $user_id)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = User::findOrFail($user_id);

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng!'], 400);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Đổi mật khẩu thành công!'], 200);
    }

    public function updateProfile(Request $request)
    {
        $user_id = $request->user()->user_id;
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user_id . ',user_id',
            'sex' => 'nullable',
            'phone' => 'nullable|string|max:10',
        ], [
            'email.unique' => 'Email đã được sử dụng.',
        ]);

        $user = User::findOrFail($user_id);
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại!'], 404);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Nếu client gửi trường sex / phone thì ghi nhận (dùng has để chấp nhận giá trị rỗng nếu cần)
        if ($request->has('sex')) {
            $data['sex'] = $request->sex;
        }
        if ($request->has('phone')) {
            $data['phone'] = $request->phone;
        }

        $user->update($data);

        return response()->json(['message' => 'Cập nhật hồ sơ thành công!', $user], 200);
    }
}
