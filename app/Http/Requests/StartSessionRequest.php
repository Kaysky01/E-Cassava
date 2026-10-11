<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'exists:devices,device_id'],
            'name' => ['required', 'string', 'max:100'],
            'cassava_condition' => ['required', 'string', 'in:fresh,stale,rotten'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
