<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:250'],
            'email' => ['required', 'string', 'max:250', 'email'],
            'title' => ['string', 'max:5000'],
            'message' => ['required', 'max:500000'],
    ];
    }

    public function messages(): array
    {
        return  [
            'name.required' => ($requiredMessage = 'Это поле необходимо для заполнения!'),
            'name.max' => ($max250 = 'Количество символов в поле не может превышать 250!'),
            'email.required' => $requiredMessage,
            'email.max' => $max250,
            'email.string' => ($requredString = 'В поле должна быть строка!'),
            'email.email' => 'Почта должна соответствовать формату mail@some.domain',
            'title.string' => $requredString,
            'title.max' => ($maxString = 'Слишком длинный текст'),
            'message.required' => $requiredMessage,
            'message.max' => $maxString,
        ];
    }
}
