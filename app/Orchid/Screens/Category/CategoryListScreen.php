<?php

namespace App\Orchid\Screens\Category;

use App\Models\Rubric;
use App\Orchid\Layouts\CreateOrUpdateCategory;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class CategoryListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'categories' => Rubric::all(),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Рубрики статей';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Создать рубрику')->modal('createCategory')
                ->method('createOrUpdateCategory'),
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
            Layout::table('categories', [
                TD::make('id', 'ID')->sort(),

                TD::make('Edit')->render(function (Rubric $rubric) {
                    return ModalToggle::make('')
                        ->modal('ediRubricCategory')
                        ->method('createOrUpdateCategory')
                        ->modalTitle('Редактирование рубрики')
                        ->asyncParameters([
                            'category' => $rubric->id
                        ])->icon('pencil');
                }),

                // TD::make('published', 'Видна?')->render(function (Rubric $rubric) {
                //     return CheckBox::make('categories[]')
                //         ->value($rubric->published)->disabled();
                // })->sort(),

                TD::make('title', 'Заголовок'),
            ]),

            Layout::modal('createCategory', CreateOrUpdateCategory::class)
                ->title('Создание рубрики')
                ->size(Modal::SIZE_LG)
                ->applyButton('Создать'),

            Layout::modal('editCategory', CreateOrUpdateCategory::class)
                ->size(Modal::SIZE_LG)
                ->async('asyncGetCategory')
        ];
    }

    public function asyncGetCategory(Rubric $rubric): array
    {
        return ['rubric' => $rubric];
    }

    public function createOrUpdateCategory(Request $request): void
    {
        $rubricId = $request->input('rubric.id');
        Rubric::updateOrCreate([
            'id' => $rubricId,
        ], [
            'title' => $request->input('rubric.title'),
        ]);

        is_null($rubricId) ? Toast::info('Рубрика создана') : Toast::info('Рубрика обновлена');
    }
}
