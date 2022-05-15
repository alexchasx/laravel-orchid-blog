<?php

namespace App\Orchid\Layouts;

use App\Models\Category;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CreateOrUpdateArticle extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Input::make('article.id')->type('hidden'),

            Group::make([
                Input::make('article.title')->required()->title('Заголовок'),
                CheckBox::make('article.published')
                    ->value(1)
                    ->sendTrueOrFalse()
                    ->title('Опубликована?'),
            ]),

            Group::make([
                Select::make('article.category_id')
                    ->fromQuery(Category::where('published', true), 'title')
                    ->title('Категория')->required(),
                DateTimer::make('article.published_at')
                    ->title('Дата публикации')
                    ->format('Y-m-d')
                    ->allowInput()
                    ->required(),
            ]),

            // TextArea::make('article.description')
            //     ->rows(8)->required()->title('Кратко'),

            // TextArea::make('article.content')
            //     ->rows(12)->required()->title('Контент'),
            // SimpleMDE::make('article.content')->title('Контент'),
            // FieldsCode::make('article.content')->language(FieldsCode::MARKUP)->title('Контент'),


            Quill::make('article.description')
                ->toolbar(["text", "color", "header", "list", "format", "media"])
                ->title('Предпросмотр')->required(),

            Quill::make('article.content')
                ->toolbar(["text", "color", "header", "list", "format", "media"])
                ->title('Контент')->required(),


            Group::make([
                Input::make('article.keywords')->title('Ключевые слова'),
                Input::make('article.meta_desc')->title('Мета деск'),
            ]),
        ];
    }
}
