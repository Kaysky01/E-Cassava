<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $device = $this->route('device');

        return auth()->check() && $device && $this->user()->can('update', $device);
    }

    /**
     * Nama boleh diubah bebas (tampilan saja). device_id yang dipakai
     * firmware dikunci saat pembuatan agar tidak putus.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
