<?php

namespace App\Orchid\Layouts;

use App\Models\Category;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
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

            Input::make('article.title')->required()->title('Заголовок'),

            TextArea::make('article.description')
                ->rows(8)->required()->title('Кратко'),

            TextArea::make('article.content')
                ->rows(12)->required()->title('Контент'),

            Select::make('article.category_id')
                ->fromQuery(Category::where('published', true), 'title')
                ->title('Категория')->required(),

            Input::make('article.keywords')->title('Ключевые слова'),

            Input::make('article.meta_desc')->title('Мета деск'),

            CheckBox::make('article.published')
                ->value(1)
                ->placeholder('Опубликована?'),

            DateTimer::make('article.published_at')
                ->title('Дата публикации')
                ->format('d-m-Y')
                ->allowInput()
                ->required(),
        ];
    }
}
