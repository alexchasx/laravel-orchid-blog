<?php

namespace App\Orchid\Layouts;

use App\Models\Rubric;
use App\Models\Tag;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\SimpleMDE;
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

                CheckBox::make('article.is_published')
                    ->value(1)
                    ->sendTrueOrFalse()
                    ->title('Опубликована?'),
            ]),

            // targetRelativeUrl(): поле отправляет относительный путь (/storage/...),
            // а не полный URL или id attachment — путь затем нормализуется на сервере.
            Picture::make('article.image')
                ->title('Изображение')
                ->storage('public')
                ->targetRelativeUrl()
                ->help('Рекомендуемый размер: 1200×630 (1.9:1), JPG/PNG/WEBP до 2 МБ'),

            Group::make([

                Select::make('article.rubric_id')
                    ->title('Рубрика')
                    ->required()
                    ->fromModel(Rubric::class, 'title')
                    ->empty('Не выбрано'),

                Select::make('article.tags')
                    ->title('Метка (тэг)')
                    ->required()
                    ->multiple()
                    ->rows(5)
                    ->fromModel(Tag::class, 'title')
                    ->empty('Не выбрано'),
            ]),

            DateTimer::make('article.published_at')
                ->title('Дата публикации')
                ->format('Y-m-d')
                    ->allowInput()
                    ->required(),

            Input::make('article.excerpt')->required()->title('Краткое описание'),

            SimpleMDE::make('article.content_raw')->title('Контент'),

            Group::make([
                Input::make('article.slug')
                    ->title('Slug (URL)')
                    ->placeholder('Оставьте пустым — сгенерируется автоматически'),
                Input::make('article.keywords')->title('Ключевые слова'),
            ]),

            // Счётчик символов для meta_desc реализован в
            // resources/views/orchid/includes/meta-desc-counter.blade.php
            // (класс .js-meta-desc-counter, логика — в том же партиале).
            Input::make('article.meta_desc')
                ->title('Мета деск')
                ->maxlength(255)
                ->placeholder('Описание для поисковых систем')
                ->help('<span class="js-meta-desc-counter">0 / 150</span>'),
        ];
    }
}
