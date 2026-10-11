<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSensorDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Device authentication can be added here in future phases (API key / device token)
        return true;
    }

    /**
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:50'],
            'api_key' => ['nullable', 'string', 'max:100'],
            'mq2' => ['required', 'integer', 'min:0', 'max:4095'],
            'mq3' => ['required', 'integer', 'min:0', 'max:4095'],
            'mq135' => ['required', 'integer', 'min:0', 'max:4095'],
            'mq2_voltage' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'mq3_voltage' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'mq135_voltage' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'temperature' => ['nullable', 'numeric', 'min:-50', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_id.required' => 'Device ID wajib diisi.',
            'mq2.required' => 'Nilai sensor MQ-2 wajib diisi.',
            'mq2.integer' => 'Nilai MQ-2 harus berupa bilangan bulat.',
            'mq2.min' => 'Nilai MQ-2 tidak boleh kurang dari 0.',
            'mq2.max' => 'Nilai MQ-2 tidak boleh lebih dari 4095.',
            'mq3.required' => 'Nilai sensor MQ-3 wajib diisi.',
            'mq3.integer' => 'Nilai MQ-3 harus berupa bilangan bulat.',
            'mq3.min' => 'Nilai MQ-3 tidak boleh kurang dari 0.',
            'mq3.max' => 'Nilai MQ-3 tidak boleh lebih dari 4095.',
            'mq135.required' => 'Nilai sensor MQ-135 wajib diisi.',
            'mq135.integer' => 'Nilai MQ-135 harus berupa bilangan bulat.',
            'mq135.min' => 'Nilai MQ-135 tidak boleh kurang dari 0.',
            'mq135.max' => 'Nilai MQ-135 tidak boleh lebih dari 4095.',
        ];
    }
}
