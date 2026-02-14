<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnverifiedEmailRequest extends FormRequest
{
    /**
     * Only allow authenticated users who haven't verified yet.
     */
    public function authorize(): bool
    {
        return $this->user() && ! $this->user()->hasVerifiedEmail();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,strict',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
