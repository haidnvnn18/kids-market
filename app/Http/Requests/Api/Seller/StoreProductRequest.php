<?php

namespace App\Http\Requests\Api\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'                         => ['required', 'exists:categories,id'],
            'name'                                => ['required', 'string', 'max:255'],
            'description'                         => ['nullable', 'string'],
            'status'                              => ['required', 'in:active,hidden'],

            'variants'                            => ['required', 'array', 'min:1'],
            'variants.*.sku'                      => ['required', 'string', 'max:100', 'distinct', 'unique:product_variants,sku'],
            'variants.*.color'                    => ['nullable', 'string', 'max:50'],
            'variants.*.size'                     => ['nullable', 'string', 'max:50'],
            'variants.*.price'                    => ['required', 'numeric', 'min:0'],
            'variants.*.wholesale_price'          => ['required', 'numeric', 'min:0'],
            'variants.*.min_quantity'             => ['required', 'integer', 'min:1'],
            'variants.*.stock'                    => ['required', 'integer', 'min:0'],

            'images'                              => ['nullable', 'array', 'max:5'],
            'images.*'                            => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
