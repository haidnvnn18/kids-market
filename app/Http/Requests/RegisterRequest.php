<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'type' => 'required|string|in:basic',
            'username' => 'required|string|min:3|max:255|unique:users',
            'password' => 'required|string|min:6',
            'fullname' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:users',
            'dob' => 'nullable|date_format:Y-m-d',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Loại đăng ký là bắt buộc',
            'type.in' => 'Loại đăng ký phải là "basic"',
            'username.required' => 'Tên đăng nhập là bắt buộc',
            'username.unique' => 'Tên đăng nhập đã được sử dụng',
            'username.min' => 'Tên đăng nhập phải có ít nhất 3 ký tự',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'fullname.required' => 'Họ tên là bắt buộc',
            'phone.unique' => 'Số điện thoại đã được sử dụng',
            'dob.date_format' => 'Định dạng ngày sinh không hợp lệ (Y-m-d)',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $validator->errors(),
        ], 422));
    }
}
