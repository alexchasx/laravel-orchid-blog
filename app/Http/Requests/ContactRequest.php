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
            'title' => ['string', 'max:5000'],
            'message' => ['required', 'max:500000'],
    ];
    }

    public function messages(): array
    {
        return  [
            'title.string' => 'В поле должна быть строка!',
            'title.max' => ($maxString = 'Слишком длинный текст'),
            'message.required' => 'Это поле необходимо для заполнения!',
            'message.max' => $maxString,
        ];
    }
}
