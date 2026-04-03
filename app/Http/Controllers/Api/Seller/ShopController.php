<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\UpdateShopRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user     = $request->attributes->get('auth_user');
        $supplier = $user->supplier;

        return response()->json([
            'success' => true,
            'data'    => $this->formatShop($supplier, $user),
        ]);
    }

    public function update(UpdateShopRequest $request): JsonResponse
    {
        $user     = $request->attributes->get('auth_user');
        $supplier = $user->supplier;

        $supplier->brand_name  = $request->brand_name;
        $supplier->country     = $request->country;
        $supplier->description = $request->description;

        if ($request->filled('avatar_data')) {
            $path = $this->saveBase64Image($request->avatar_data, 'suppliers/avatars');
            if ($path) {
                if ($supplier->avatar && Storage::disk('public')->exists($supplier->avatar)) {
                    Storage::disk('public')->delete($supplier->avatar);
                }
                $supplier->avatar = $path;
            }
        }

        if ($request->filled('banner_data')) {
            $path = $this->saveBase64Image($request->banner_data, 'suppliers/banners');
            if ($path) {
                if ($supplier->banner && Storage::disk('public')->exists($supplier->banner)) {
                    Storage::disk('public')->delete($supplier->banner);
                }
                $supplier->banner = $path;
            }
        }

        $supplier->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin shop thành công',
            'data'    => $this->formatShop($supplier->fresh(), $user),
        ]);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user     = $request->attributes->get('auth_user');
        $supplier = $user->supplier;

        if ($supplier->avatar && Storage::disk('public')->exists($supplier->avatar)) {
            Storage::disk('public')->delete($supplier->avatar);
        }
        $supplier->avatar = null;
        $supplier->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ảnh đại diện',
        ]);
    }

    public function deleteBanner(Request $request): JsonResponse
    {
        $user     = $request->attributes->get('auth_user');
        $supplier = $user->supplier;

        if ($supplier->banner && Storage::disk('public')->exists($supplier->banner)) {
            Storage::disk('public')->delete($supplier->banner);
        }
        $supplier->banner = null;
        $supplier->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ảnh banner',
        ]);
    }

    private function saveBase64Image(string $base64Data, string $directory): ?string
    {
        $imageData = preg_replace('/^data:image\/[^;]+;base64,/', '', $base64Data);
        $decoded   = base64_decode($imageData, true);

        if ($decoded === false || strlen($decoded) > 5 * 1024 * 1024) {
            return null;
        }

        $extension = 'jpg';
        if (str_contains($base64Data, 'data:image/png')) {
            $extension = 'png';
        } elseif (str_contains($base64Data, 'data:image/gif')) {
            $extension = 'gif';
        } elseif (str_contains($base64Data, 'data:image/webp')) {
            $extension = 'webp';
        }

        $filename = Str::uuid() . '.' . $extension;
        $path     = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $decoded);

        return $path;
    }

    private function formatShop($supplier, $user): array
    {
        return [
            'id'          => $supplier->id,
            'brand_name'  => $supplier->brand_name,
            'country'     => $supplier->country,
            'description' => $supplier->description,
            'rating_avg'  => (float) $supplier->rating_avg,
            'avatar'      => $supplier->avatar ? asset('storage/' . $supplier->avatar) : null,
            'banner'      => $supplier->banner ? asset('storage/' . $supplier->banner) : null,
            'owner'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ];
    }
}
