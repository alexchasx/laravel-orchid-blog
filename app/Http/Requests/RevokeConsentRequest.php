<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevokeConsentRequest extends FormRequest
{
    /**
     * Получить правила валидации.
     */
    public function rules(): array
    {
        return [
            'comment_id'  => 'required|integer|exists:comments,id',
            'consent_type' => 'required|in:processing,distribution',
            'email'       => 'required|email',
        ];
    }

    /**
     * Сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'comment_id.exists'  => 'Комментарий не найден.',
            'consent_type.in'    => 'Некорректный тип согласия.',
            'email.required'     => 'Укажите e-mail владельца комментария.',
            'email.email'        => 'Некорректный формат e-mail.',
        ];
    }
}
