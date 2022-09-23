<?php

namespace App\Orchid\Screens\Contact;

use App\Models\Contact;
use App\Models\User;
use Orchid\Screen\Screen;
use App\Orchid\Layouts\ShowContact;
use Carbon\Carbon;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Sight;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Color;

class ContactListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'contacts' => Contact::filters()->defaultSort('created_at', 'desc')
                ->paginate(24),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Сообщения через "Обратная связь"';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('contacts', [
                TD::make('id', 'ID'),

                TD::make('Смотреть')->render(function (Contact $contact) {
                    return Link::make('')
                        ->route('platform.contact', ['id' => $contact->id])
                        ->icon('eye');

                }),

                // TD::make('read', 'Прочитано')->render(function (Contact $contact) {
                //     return CheckBox::make('contacts[]')
                //         ->value($contact->read);
                // })->sort(),

                TD::make('name', 'Имя')
                    ->render(function (Contact $contact) {
                        return Str::limit(e($contact->user->name), 30);
                    }),

                TD::make('title', 'Заголовок')
                        ->render(function (Contact $contact) {
                            return Str::limit(e($contact->title), 40);
                        }),

                TD::make('message', 'Сообщение')
                    ->render(function (Contact $contact) {
                        return Str::limit(e($contact->message), 50);
                    }),

                TD::make('email', 'Email')->defaultHidden(),

                TD::make('created_at', 'Дата создания')->render(function (Contact $contact) {
                    $carbon = Carbon::create($contact->created_at);

                    return $carbon->format('d.m.Y H:i');
                }),

            // TD::make('delete', 'Удалить'),
            ]),
        ];
    }

    // public function asyncGetContact(Contact $contact): array
    // {
    //     return ['contact' => $contact];
    // }
}
