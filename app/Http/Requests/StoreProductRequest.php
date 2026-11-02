<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->ignore($this->route('product')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer'],
            'cost' => ['required', 'integer', 'min:0'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
        ];
    }
}
