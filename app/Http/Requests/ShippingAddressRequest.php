<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShippingAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'      => 'required|string|max:100',
            'phone'          => 'required|string|max:20|regex:/^[0-9\-\+\s\(\)]{8,}$/',
            'province_id'    => 'required|integer|exists:provinces,id',
            'district_id'    => 'required|integer|exists:districts,id',
            'address_detail' => 'required|string|max:255',
            'is_default'     => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'      => 'Họ và tên người nhận là bắt buộc',
            'full_name.max'           => 'Họ và tên không được vượt quá 100 ký tự',
            'phone.required'          => 'Số điện thoại là bắt buộc',
            'phone.regex'             => 'Số điện thoại không hợp lệ',
            'phone.max'               => 'Số điện thoại không được vượt quá 20 ký tự',
            'province_id.required'    => 'Tỉnh/Thành phố là bắt buộc',
            'province_id.exists'      => 'Tỉnh/Thành phố không tồn tại',
            'district_id.required'    => 'Phường/Xã/Đặc khu là bắt buộc',
            'district_id.exists'      => 'Phường/Xã/Đặc khu không tồn tại',
            'address_detail.required' => 'Địa chỉ chi tiết là bắt buộc',
            'address_detail.max'      => 'Địa chỉ chi tiết không được vượt quá 255 ký tự',
            'is_default.boolean'      => 'Giá trị mặc định không hợp lệ',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
