<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\Models\Role;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make(__('На главную'))
                ->icon('bs.house')
                ->route('home'),

            Menu::make(__('Обратная связь'))
                ->icon('bs.envelope')
                ->route('platform.contact.list')
                ->badge(function () {
                    return Contact::all('id')->count();
                }),

            Menu::make(__('Статьи'))
                ->icon('bs.file-earmark-text')
                ->permission('platform.custom.articles')
                ->route('platform.articles')
                ->badge(function () {
                    return Article::all('id')->count();
                }),

            Menu::make(__('Рубрики'))
                ->icon('bs.list-ul')
                ->permission('platform.custom.rubrics')
                ->route('platform.rubric.list')
                ->badge(function () {
                    return Rubric::all('id')->count();
                }),

            Menu::make(__('Метки'))
                ->icon('bs.tags')
                ->permission('platform.custom.rubrics')
                ->route('platform.tag.list')
                ->badge(function () {
                    return Tag::all('id')->count();
                }),

            Menu::make(__('Комментарии'))
                ->icon('bs.chat-left-text')
                ->route('platform.comment.list')
                ->permission('platform.custom.comments')
                ->badge(function () {
                    return Comment::all('id')->count();
                }),

            Menu::make(__('Пользователи'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access rights'))
                ->badge(function () {
                    return User::all('id')->count();
                }),

            Menu::make(__('Роли'))
                ->icon('bs.shield-lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->badge(function () {
                    return Role::all('id')->count();
                }),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
            ItemPermission::group(__('Дополнительные'))
                ->addPermission('platform.custom.articles', __('Статьи'))
                ->addPermission('platform.custom.rubrics', __('Рубрики и метки'))
                ->addPermission('platform.custom.comments', __('Комментарии')),
        ];
    }
}
