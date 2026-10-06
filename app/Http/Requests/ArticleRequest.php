<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class ArticleRequest extends FormRequest
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
            'article.title' => ['required', 'string', 'max:255'],
            // 'article.excerpt' => ['required'],
            // Без подчёркиваний в числе: 'max:1_000_000' парсится Laravel как лимит 1.
            'article.content_raw' => ['required', 'string', 'max:1000000'],
            'article.rubric_id' => ['required', 'integer', 'exists:rubrics,id'],
            'article.published_at' => ['required', 'date_format:Y-m-d'],
            'article.slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'article.tags' => ['nullable', 'array'],
            'article.tags.*' => ['integer', 'exists:tags,id'],
            // Поле Picture Orchid присылает НЕ файл, а строку с путём/URL изображения
            // (сам файл загружается отдельным AJAX-запросом). Поэтому валидируем строку,
            // а для прямых отправок файла (API/тесты) применяем правила изображения.
            'article.image' => ['nullable', function (string $attribute, mixed $value, Closure $fail): void {
                if ($value instanceof UploadedFile) {
                    $validator = Validator::make(
                        ['image' => $value],
                        ['image' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]
                    );

                    foreach ($validator->errors()->get('image') as $message) {
                        $fail($message);
                    }

                    return;
                }

                if (!is_string($value) || mb_strlen($value) > 2048) {
                    $fail('Изображение должно быть файлом JPG/PNG/WEBP до 2 МБ или путём к нему.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return  [
            'article.slug.regex' => 'Slug может содержать только строчные латинские буквы, цифры и дефисы.',
            // 'article.title.title' => 'Количество символов должно быть менее 255',
            // 'article.content.content' => 'Количество символов должно быть менее 255'
        ];
    }
}
