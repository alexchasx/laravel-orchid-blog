<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
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

            Input::make('tag.title')->required()->title('Заголовок'),

            // Input::make('tag.popular')->title('Популярность'),

            CheckBox::make('tag.active')
                ->value(1)
                ->sendTrueOrFalse()
                ->placeholder('Активная?'),
        ];
    }
}
