<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email'             => ['required', 'email', 'max:255'],
            'consent_processing' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'             => 'Поле "Email" обязательно.',
            'email.email'                => 'Введите корректный email.',
            'email.max'                  => 'Email слишком длинный.',
            'consent_processing.required' => 'Необходимо дать согласие на обработку персональных данных.',
            'consent_processing.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
        ];
    }
}
