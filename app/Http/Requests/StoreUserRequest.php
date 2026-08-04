<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['super_admin', 'warehouse_admin', 'staff'])],
            'branch_id' => ['required_unless:role,super_admin', 'nullable', 'integer', 'exists:branches,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required_unless' => 'Cabang wajib dipilih untuk role ini.',
        ];
    }
}