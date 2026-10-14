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

        // subject_type: comment, subscriber или contact.
        $rules['subject_type'] = 'required|in:' . ConsentLog::SUBJECT_COMMENT . ',' . ConsentLog::SUBJECT_SUBSCRIBER . ',' . ConsentLog::SUBJECT_CONTACT;

        // comment_id обязателен только для комментариев.
        if ($this->input('subject_type') === ConsentLog::SUBJECT_COMMENT) {
            $rules['comment_id'] = 'required|integer|exists:comments,id';
        }

        // contact_id обязателен только для обращений.
        if ($this->input('subject_type') === ConsentLog::SUBJECT_CONTACT) {
            $rules['contact_id'] = 'required|integer|exists:contacts,id';
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
            'contact_id.exists'  => 'Обращение не найдено.',
            'consent_type.in'    => 'Некорректный тип согласия.',
            'email.required'     => 'Укажите e-mail.',
            'email.email'        => 'Некорректный формат e-mail.',
            'subject_type.in'    => 'Некорректный тип субъекта.',
        ];
    }
}
