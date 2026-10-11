<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Nama alat sekaligus menjadi device_id yang dikirim firmware ESP32,
     * jadi harus unik dan maks 50 karakter (batas payload sensor).
     *
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('devices', 'device_id')],
            'location' => ['nullable', 'string', 'max:150'],
        ];
    }
}
