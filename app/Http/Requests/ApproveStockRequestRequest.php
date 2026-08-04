<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveStockRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_request_item_id' => ['required', 'integer', 'exists:stock_request_items,id'],
            'items.*.decision' => ['required', 'in:approve,reject'],
            'items.*.source_branch_id' => ['required_if:items.*.decision,approve', 'nullable', 'integer', 'exists:branches,id'],
            'items.*.quantity_approved' => ['required_if:items.*.decision,approve', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.source_branch_id.required_if' => 'Cabang sumber wajib diisi kalau item disetujui.',
            'items.*.quantity_approved.required_if' => 'Jumlah yang disetujui wajib diisi kalau item disetujui.',
        ];
    }
}