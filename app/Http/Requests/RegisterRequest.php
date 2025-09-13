<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $passwordRules = config('validation.password_rules', []);
        $passwordRules[] = 'same:confirmPassword';

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => $passwordRules,
            'confirmPassword' => config('validation.password_confirmation_rules', ['required', 'string']),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.regex' => 'The password must contain at least one lowercase letter,
                                    one uppercase letter, one number, and one special character.',
            'password.same' => 'The password and confirm password must match.',
        ];
    }
}
