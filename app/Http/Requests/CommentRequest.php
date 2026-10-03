<?php

namespace App\Http\Requests;

use App\Rules\MathCaptchaRule;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
        $rules = [
            'article_id' => ['required', 'exists:articles,id'],
            // 'string' — защита от передачи массива вместо текста комментария.
            'comment' => ['required', 'string', 'max:5000'],
        ];

        if (! $this->user()) {
            $rules['name'] = ['required', 'string', 'min:2', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
            $rules['captcha'] = ['required', new MathCaptchaRule()];
        }

        // Согласия на обработку ПДн (152-ФЗ) — обязательны всегда.
        $rules['consent_processing'] = ['required', 'accepted'];
        $rules['consent_distribution'] = ['required', 'accepted'];
        $rules['distribution_conditions'] = [
            'nullable',
            'string',
            'max:' . config('consent.distribution.max_conditions_length', 1000),
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'Это поле необходимо для заполнения',
            'comment.max' => 'Комментарий не должен превышать 5000 символов.',
            'article_id.required' => 'Не указана статья, к которой относится комментарий.',
            'article_id.exists' => 'Указанная статья не найдена.',
            'name.required' => 'Поле "Имя" обязательно.',
            'name.min' => 'Имя должно содержать не менее 2 символов.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Поле "Email" обязательно.',
            'email.email' => 'Введите корректный email.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'captcha.required' => 'Ответьте на контрольный вопрос.',
            'consent_processing.required' => 'Чтобы отправить комментарий, подтвердите оба согласия: на обработку и на распространение персональных данных — отметьте нужные галочки.',
            'consent_processing.accepted' => 'Чтобы отправить комментарий, подтвердите оба согласия: на обработку и на распространение персональных данных — отметьте нужные галочки.',
            'consent_distribution.required' => 'Чтобы отправить комментарий, подтвердите оба согласия: на обработку и на распространение персональных данных — отметьте нужные галочки.',
            'consent_distribution.accepted' => 'Чтобы отправить комментарий, подтвердите оба согласия: на обработку и на распространение персональных данных — отметьте нужные галочки.',
            'distribution_conditions.max' => 'Дополнительные условия не должны превышать :max символов.',
        ];
    }
}
