<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    //
    public function upload(Request $request)
    {
        // Normalize: hỗ trợ cả key 'images' lẫn 'images[]'
        $allFiles = $request->allFiles();
        $files = [];
        foreach (['images', 'images[]'] as $key) {
            if (!empty($allFiles[$key])) {
                $value = $allFiles[$key];
                $files = array_merge($files, is_array($value) ? array_values($value) : [$value]);
            }
        }

        if (empty($files)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 ảnh.'], 422);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2048 * 1024; // 2MB

        foreach ($files as $index => $file) {
            if (!$file->isValid()) {
                return response()->json(['status' => false, 'message' => "File #$index không hợp lệ."], 422);
            }
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json(['status' => false, 'message' => "File #$index không đúng định dạng (jpg, jpeg, png, webp)."], 422);
            }
            if ($file->getSize() > $maxSize) {
                return response()->json(['status' => false, 'message' => "File #$index vượt quá 2MB."], 422);
            }
        }

        $uploaded = [];
        foreach ($files as $file) {
            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('images', $fileName, 'public');
            $uploaded[] = [
                'file_name' => $fileName,
                'file_path' => $path,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Upload ảnh thành công',
            'data' => $uploaded,
        ]);
    }
}
