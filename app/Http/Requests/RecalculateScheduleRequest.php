<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecalculateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is handled in the controller via Gate.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_periodic_amount' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_periodic_amount.required' => __('Please enter a new periodic saving amount.'),
            'new_periodic_amount.integer' => __('The amount must be a whole number (no decimals).'),
            'new_periodic_amount.min' => __('The amount must be at least 1.'),
        ];
    }
}
