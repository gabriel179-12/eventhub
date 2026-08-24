<?php

namespace App\Http\Requests\TicketTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'=> [
                'required',
                'string',
                'max:100',
                Rule::unique('ticket_types', 'name')
                    ->where('event_id', $this->route('event')->id),
            ],
            'description'=> ['nullable', 'string'],
            'price_in_cents'=> ['required', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'sales_starts_at' => ['nullable', 'date'],
            'sales_ends_at' => ['nullable', 'date', 'after:sales_starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
