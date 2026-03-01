<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

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
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/', 'unique:users,username'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()->uncompromised()],
            'fullname' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s().]{8,20}$/'],
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
            'username.regex' => 'Tên đăng nhập chỉ được chứa chữ, số và các ký tự . _ -',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.letters' => 'Mật khẩu phải chứa ít nhất một chữ cái',
            'password.numbers' => 'Mật khẩu phải chứa ít nhất một chữ số',
            'password.uncompromised' => 'Mật khẩu này không an toàn, vui lòng chọn mật khẩu khác',
            'fullname.required' => 'Họ tên là bắt buộc',
            'phone.regex' => 'Số điện thoại không hợp lệ',
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
