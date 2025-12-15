<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Addresses;

class AddressController extends Controller
{
    public function getProvinces()
    {
        $response = Http::get('https://esgoo.net/api-tinhthanh/1/0.htm');
        return response()->json($response->json());
    }

    public function getDistricts($provinceId)
    {
        $response = Http::get("https://esgoo.net/api-tinhthanh/2/{$provinceId}.htm");
        return response()->json($response->json());
    }
    public function getWards($districtId)
    {
        $response = Http::get("https://esgoo.net/api-tinhthanh/3/{$districtId}.htm");
        return response()->json($response->json());
    }

    public function index(Request $request)
    {
        $user_id = $request->user()->user_id;
         $address = Addresses::where('user_id', $user_id)
        ->orderByDesc('is_default') // địa chỉ mặc định lên đầu
        ->orderByDesc('created_at')
        ->get();
        return response()->json($address);
    }
    public function store(Request $request)
    {
        // 1. Validate dữ liệu gửi lên từ Vue component
        $validatedData = $request->validate([
            'fullName' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string',
            'street' => 'required|string|max:255',
            'type' => 'required|string|in:home,office',
            'isDefault' => 'required|boolean',
        ], [
            'fullName.required' => 'Họ và tên là bắt buộc.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'province.required' => 'Tỉnh/Thành phố là bắt buộc.',
            'district.required' => 'Quận/Huyện là bắt buộc.',
            'ward.required' => 'Phường/Xã là bắt buộc.',
            'street.required' => 'Địa chỉ cụ thể là bắt buộc.',
        ]);

        $user_id = $request->user()->user_id;

        // 2. Nếu địa chỉ mới được set là mặc định, hãy bỏ mặc định của các địa chỉ cũ
        if ($validatedData['isDefault']) {
            Addresses::where('user_id', $user_id)->update(['is_default' => false]);
        }

        // 3. Tạo và lưu địa chỉ mới
        $address = Addresses::create([
            'user_id' => $user_id,
            'recipient_name' => $validatedData['fullName'],
            'recipient_phone' => $validatedData['phone'],
            'street_address' => $validatedData['street'],
            'ward' => $validatedData['ward'],
            'district' => $validatedData['district'],
            'province' => $validatedData['province'],
            'type' => $validatedData['type'],
            'is_default' => $validatedData['isDefault'],
        ]);
        return response()->json(['message' => 'Thêm địa chỉ thành công!', 'address' => $address, 'status' => true], 201);
    }
    public function update(Request $request, $id)
    {
         $validatedData = $request->validate([
            'fullName' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string',
            'street' => 'required|string|max:255',
            'type' => 'required|string|in:home,office',
            'isDefault' => 'required|boolean',
        ], [
            'fullName.required' => 'Họ và tên là bắt buộc.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'province.required' => 'Tỉnh/Thành phố là bắt buộc.',
            'district.required' => 'Quận/Huyện là bắt buộc.',
            'ward.required' => 'Phường/Xã là bắt buộc.',
            'street.required' => 'Địa chỉ cụ thể là bắt buộc.',
        ]);
        $user_id = $request->user()->user_id;

        // 2. Nếu địa chỉ mới được set là mặc định, hãy bỏ mặc định của các địa chỉ cũ
        if ($validatedData['isDefault']) {
            Addresses::where('user_id', $user_id)->update(['is_default' => false]);
        }

        $address = Addresses::findOrFail($id);
        $address->update([
            'user_id' => $user_id,
            'recipient_name' => $validatedData['fullName'],
            'recipient_phone' => $validatedData['phone'],
            'street_address' => $validatedData['street'],
            'ward' => $validatedData['ward'],
            'district' => $validatedData['district'],
            'province' => $validatedData['province'],
            'type' => $validatedData['type'],
            'is_default' => $validatedData['isDefault'],
        ]);
        return response()->json(['message' => 'Cập nhật địa chỉ thành công!', 'address' => $address, 'status' => true]);

    }
    public function destroy($id)
    {
        $address = Addresses::findOrFail($id);
        $address->delete();
        return response()->json(['message' => 'Xóa địa chỉ thành công!', 'status' => true]);
    }
    public function setDefault(Request $request, $id)
    {
        $user_id = $request->user()->user_id;
        Addresses::where('user_id', $user_id)->update(['is_default' => false]);

        $address = Addresses::findOrFail($id);
        $address->update(['is_default' => true]);
        return response()->json(['message' => 'Đặt địa chỉ mặc định thành công!', 'address' => $address, 'status' => true]);
    }
}
