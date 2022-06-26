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
            'article.title' => ['required', 'max:255'],
            // 'article.excert' => ['required'],
            'article.content_raw' => ['required'],
            'article.rubric_id' => ['required'],
            'article.published_at' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function messages(): array
    {
        return  [
            // 'article.title.title' => 'Количество символов должно быть менее 255',
            // 'article.content.content' => 'Количество символов должно быть менее 255'
        ];
    }
}
