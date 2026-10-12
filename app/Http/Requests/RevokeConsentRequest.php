<?php

namespace App\Http\Requests;

use App\Models\ConsentLog;
use Illuminate\Foundation\Http\FormRequest;

class RevokeConsentRequest extends FormRequest
{
    /**
     * Получить правила валидации.
     */
    public function rules(): array
    {
        $rules = [
            'consent_type' => 'required|in:processing,distribution',
            'email'       => 'required|email',
        ];

        // subject_type: comment или subscriber.
        $rules['subject_type'] = 'required|in:' . ConsentLog::SUBJECT_COMMENT . ',' . ConsentLog::SUBJECT_SUBSCRIBER;

        // comment_id обязателен только для комментариев.
        if ($this->input('subject_type') === ConsentLog::SUBJECT_COMMENT) {
            $rules['comment_id'] = 'required|integer|exists:comments,id';
        }

        return $rules;
    }

    /**
     * Сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'comment_id.exists'  => 'Комментарий не найден.',
            'consent_type.in'    => 'Некорректный тип согласия.',
            'email.required'     => 'Укажите e-mail.',
            'email.email'        => 'Некорректный формат e-mail.',
            'subject_type.in'    => 'Некорректный тип субъекта.',
        ];
    }
}
