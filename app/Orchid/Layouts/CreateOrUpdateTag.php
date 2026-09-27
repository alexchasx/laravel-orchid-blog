<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class CreateOrUpdateTag extends Rows
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
            Input::make('tag.id')->type('hidden'),

            Input::make('tag.slug')->title('Слаг'),

            Input::make('tag.title')->required()->title('Заголовок'),

            TextArea::make('tag.description')
                ->title('Описание')
                ->rows(3)
                ->placeholder('Краткое описание метки — используется как meta description страницы метки'),

            // Input::make('tag.popular')->title('Популярность'),

            CheckBox::make('tag.active')
                ->value(1)
                ->sendTrueOrFalse()
                ->placeholder('Активная?'),
        ];
    }
}
