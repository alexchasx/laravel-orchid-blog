<?php

namespace App\Orchid\Screens\Tag;

use App\Models\Tag;
use App\Orchid\Layouts\CreateOrUpdateTag;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Toast;

class TagListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return ['tags' => Tag::all()];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Метки';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Создать метку')->modal('createTag')
                ->method('createOrUpdateTag'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('tags', [
                TD::make('id', 'ID')->sort(),

                TD::make('Edit')->render(function (Tag $tag) {
                    return ModalToggle::make('')
                        ->modal('editTag')
                        ->method('createOrUpdateTag')
                        ->modalTitle('Редактирование метки')
                        ->asyncParameters([
                            'tag' => $tag->id
                        ])->icon('pencil');
                }),

                TD::make('active', 'Видна?')->render(function (Tag $tag) {
                    return CheckBox::make('tags[]')
                        ->value($tag->active)
                        ->disabled();
                })->sort(),

                TD::make('title', 'Заголовок'),

                TD::make('popular', 'Популярность')->render(function (Tag $tag) {
                    return Input::make('tags[]')
                        ->value($tag->popular)
                        ->disabled()
                        ->type('number');
                })->width(50)->sort(),
            ]),

            Layout::modal('createTag', CreateOrUpdateTag::class)
                ->title('Создание метки')
                ->applyButton('Создать'),

            Layout::modal('editTag', CreateOrUpdateTag::class)
                ->async('asyncGetTag')
        ];
    }

    public function asyncGetTag(Tag $tag): array
    {
        return compact('tag');
    }

    public function createOrUpdateTag(Request $request): void
    {
        $request->validate([
            'tag.title' => ['required', 'min:3', 'max:254'],
            'tag.popular' => ['min:0', 'numeric'],
            'tag.active' => ['required', 'boolean'],
        ]);

        $tagId = $request->input('tag.id');

        Tag::updateOrCreate([
            'id' => $tagId,
        ], [
            'title' => $request->input('tag.title'),
            'popular' => $request->input('tag.popular'),
            'active' => $request->boolean('tag.active'),
        ]);

        is_null($tagId) ? Toast::info('Метка создана') : Toast::info('Метка обновлена');
    }
}
