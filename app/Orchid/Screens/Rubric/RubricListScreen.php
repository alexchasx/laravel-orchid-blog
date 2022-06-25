<?php

namespace App\Orchid\Screens\Rubric;

use App\Models\Rubric;
use App\Orchid\Layouts\CreateOrUpdateRubric;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class RubricListScreen extends Screen
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
            ModalToggle::make('Создать рубрику')->modal('createRubric')
                ->method('createOrUpdateRubric'),
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
                        ->modal('editRubric')
                        ->method('createOrUpdateRubric')
                        ->modalTitle('Редактирование рубрики')
                        ->asyncParameters([
                            'rubric' => $rubric->id
                        ])->icon('pencil');
                }),

                // TD::make('published', 'Видна?')->render(function (Rubric $rubric) {
                //     return CheckBox::make('categories[]')
                //         ->value($rubric->published)->disabled();
                // })->sort(),

                TD::make('title', 'Заголовок'),
            ]),

            Layout::modal('createRubric', CreateOrUpdateRubric::class)
                ->title('Создание рубрики')
                ->size(Modal::SIZE_LG)
                ->applyButton('Создать'),

            Layout::modal('editRubric', CreateOrUpdateRubric::class)
                ->size(Modal::SIZE_LG)
                ->async('asyncGetRubric')
        ];
    }

    public function asyncGetRubric(Rubric $rubric): array
    {
        return ['rubric' => $rubric];
    }

    public function createOrUpdateRubric(Request $request): void
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
