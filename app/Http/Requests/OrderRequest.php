<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping_address_id' => 'required|integer|exists:shipping_addresses,id',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cart_item_id' => 'nullable|integer|exists:cart_items,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'shipping_address_id.required' => 'Địa chỉ giao hàng là bắt buộc',
            'shipping_address_id.exists' => 'Địa chỉ giao hàng không tồn tại',
            'items.required' => 'Danh sách sản phẩm là bắt buộc',
            'items.array' => 'Danh sách sản phẩm không hợp lệ',
            'items.min' => 'Danh sách sản phẩm không được rỗng',
            'items.*.product_variant_id.required' => 'Mã biến thể sản phẩm là bắt buộc',
            'items.*.product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại',
            'items.*.quantity.required' => 'Số lượng là bắt buộc',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',
            'items.*.cart_item_id.integer' => 'ID mục giỏ hàng phải là số',
            'items.*.cart_item_id.exists' => 'Mục giỏ hàng không tồn tại',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
