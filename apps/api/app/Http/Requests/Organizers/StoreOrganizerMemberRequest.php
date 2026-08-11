<?php

namespace App\Http\Requests\Organizers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use illuminate\Validation\Rulw;

final class StoreOrganizerMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return[
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::exists('users', 'email'),
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['manager', 'checkin_staff']),
            ],
        ];
    }
}