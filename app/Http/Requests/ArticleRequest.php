<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            // 'article.excert' => ['required'],
            // Без подчёркиваний в числе: 'max:1_000_000' парсится Laravel как лимит 1.
            'article.content_raw' => ['required', 'string', 'max:1000000'],
            'article.rubric_id' => ['required', 'integer', 'exists:rubrics,id'],
            'article.published_at' => ['required', 'date_format:Y-m-d'],
            'article.slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'article.tags' => ['nullable', 'array'],
            'article.tags.*' => ['integer', 'exists:tags,id'],
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
