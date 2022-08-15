<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\Models\Role;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * @param Dashboard $dashboard
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * @return Menu[]
     */
    public function registerMainMenu(): array
    {
        return [
            Menu::make(__('На главную'))
                ->icon('home')
                ->route('home'),

            Menu::make(__('Обратная связь'))
                ->icon('envelope-letter')
                // ->permission('platform.custom.articles')
                // ->canSee(Auth::user()->hasAccess('platform.custom.articles'))
                ->route('platform.contact.list')
                ->badge(function () {
                    return Contact::all('id')->count();
                }),

            Menu::make(__('Статьи'))
                ->icon('paste')
                ->permission('platform.custom.articles')
                ->route('platform.articles')
                ->badge(function () {
                    return Article::all('id')->count();
                }),

            Menu::make(__('Рубрики'))
                ->icon('list')
                ->permission('platform.custom.rubrics')
                ->route('platform.rubric.list')
                ->badge(function () {
                    return Rubric::all('id')->count();
                }),

            Menu::make(__('Метки'))
                ->icon('tag')
                ->permission('platform.custom.rubrics')
                ->route('platform.tag.list')
                ->badge(function () {
                    return Tag::all('id')->count();
                }),

            Menu::make(__('Комментарии'))
                ->icon('bubble')
                ->route('platform.comment.list')
                ->permission('platform.custom.comments')
                ->badge(function () {
                    return Comment::all('id')->count();
                }),

            Menu::make(__('Пользователи'))
                ->icon('user')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access rights'))
                ->badge(function () {
                    return User::all('id')->count();
                }),

            Menu::make(__('Роли'))
                ->icon('lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->badge(function () {
                    return Role::all('id')->count();
                }),


            // Examples:

            // Menu::make(__(''))
            //     ->title('========= Examples ========='),

            // Menu::make('Example screen')
            //     ->icon('monitor')
            //     ->route('platform.example')
            //     ->title('Navigation')
            //     ->badge(function () {
            //         return 6;
            //     }),

            // Menu::make('Dropdown menu')
            //     ->icon('code')
            //     ->list([
            //         Menu::make('Sub element item 1')->icon('bag'),
            //         Menu::make('Sub element item 2')->icon('heart'),
            //     ]),

            // Menu::make('Basic Elements')
            //     ->title('Form controls')
            //     ->icon('note')
            //     ->route('platform.example.fields'),

            // Menu::make('Advanced Elements')
            //     ->icon('briefcase')
            //     ->route('platform.example.advanced'),

            // Menu::make('Text Editors')
            //     ->icon('list')
            //     ->route('platform.example.editors'),

            // Menu::make('Overview layouts')
            //     ->title('Layouts')
            //     ->icon('layers')
            //     ->route('platform.example.layouts'),

            // Menu::make('Chart tools')
            //     ->icon('bar-chart')
            //     ->route('platform.example.charts'),

            // Menu::make('Cards')
            //     ->icon('grid')
            //     ->route('platform.example.cards')
            //     ->divider(),

            // Menu::make('Documentation')
            //     ->title('Docs')
            //     ->icon('docs')
            //     ->url('https://orchid.software/en/docs'),

            // Menu::make('Changelog')
            //     ->icon('shuffle')
            //     ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
            //     ->target('_blank')
            //     ->badge(function () {
            //         return Dashboard::version();
            //     }, Color::DARK()),

        ];
    }

    /**
     * @return Menu[]
     */
    public function registerProfileMenu(): array
    {
        return [
            Menu::make('Profile')
                ->route('platform.profile')
                ->icon('user'),
        ];
    }

    /**
     *
     * @return ItemPermission[]
     */
    public function registerPermissions(): array
    {
        return [
            /**
             * Все права (permissions):
             * "platform.index"
             * "platform.systems.roles"
             * "platform.systems.users"
             * "platform.custom.rubrics"
             * "platform.custom.articles"
             * "platform.custom.comments"
             * "platform.systems.attachment"
             * "platform.systems.settings"
             * "platform.systems.media"
             */
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
            ItemPermission::group(__('Дополнительные'))
                ->addPermission('platform.custom.articles', __('Статьи'))
                // ->addPermission('platform.custom.contacts', __('Контакты'))
                ->addPermission('platform.custom.rubrics', __('Рубрики и метки'))
                ->addPermission('platform.custom.comments', __('Комментарии')),
        ];
    }
}
