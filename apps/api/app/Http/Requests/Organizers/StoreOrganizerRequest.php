<?php

namespace App\Http\Requests\Organizers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrganizerRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'name' => ['required', 'string', 'max:150'],
           'slug' => [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('organizers', 'slug'),
           ],
           'description' => ['nullable', 'string'],
           'social_links' => ['nullable', 'array'],
           'social_links.instagram' => ['nullable', 'url', 'max:255'],
           'social_links.facebook' => ['nullable', 'url', 'max:255'],
           'social_links.linkedin' => ['nullable', 'url', 'max:255'],
           'social_links.website' => ['nullable', 'url', 'max:255'],
        ];

    }
}
