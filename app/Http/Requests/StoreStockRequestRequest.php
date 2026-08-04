<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity_requested' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Pengajuan harus berisi minimal 1 produk.',
            'items.*.product_id.exists' => 'Salah satu produk yang dipilih tidak valid.',
            'items.*.quantity_requested.min' => 'Jumlah yang diminta harus lebih dari 0.',
        ];
    }
}