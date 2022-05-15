<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CreateOrUpdateCategory extends Rows
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
            Input::make('category.id')->type('hidden'),

            Input::make('category.title')->required()->title('Заголовок'),

            CheckBox::make('category.published')
                ->value(1)
                ->sendTrueOrFalse()
                ->placeholder('Опубликована?'),
        ];
    }
}
