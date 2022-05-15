<?php

namespace App\Orchid\Screens\Category;

use App\Models\Category;
use App\Orchid\Layouts\CreateOrUpdateCategory;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
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
            'categories' => Category::all(),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Категории статей';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Создать категорию')->modal('createCategory')
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

                TD::make('Edit')->render(function (Category $category) {
                    return ModalToggle::make('')
                        ->modal('editCategory')
                        ->method('createOrUpdateCategory')
                        ->modalTitle('Редакт.')
                        ->asyncParameters([
                            'category' => $category->id
                        ])->icon('pencil');
                }),

                TD::make('published', 'Видна?')->render(function (Category $category) {
                    return CheckBox::make('categories[]')
                        ->value($category->published)->disabled();
                })->sort(),

                TD::make('title', 'Заголовок'),
            ]),

            Layout::modal('createCategory', CreateOrUpdateCategory::class)
                ->title('Создание категории')
                ->size(Modal::SIZE_LG)
                ->applyButton('Создать'),

            Layout::modal('editCategory', CreateOrUpdateCategory::class)
                ->size(Modal::SIZE_LG)
                ->async('asyncGetCategory')
        ];
    }

    public function asyncGetCategory(Category $category): array
    {
        return ['category' => $category];
    }

    public function createOrUpdateCategory(Request $request): void
    {
        $categoryId = $request->input('category.id');
        Category::updateOrCreate([
            'id' => $categoryId,
        ], [
            'title' => $request->input('category.title'),
            'published' => $request->boolean('category.published'),
        ]);

        is_null($categoryId) ? Toast::info('Статья создана') : Toast::info('Статья обновлена');
    }
}
