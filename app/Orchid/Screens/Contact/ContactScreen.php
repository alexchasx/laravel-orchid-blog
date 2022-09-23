<?php

namespace App\Orchid\Screens\Contact;

use App\Models\Contact;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Sight;

class ContactScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query($id): iterable
    {
        return [
            'contact' => Contact::findOrFail($id),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'ContactScreen';
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
            Layout::legend('contact', [
                Sight::make('id'),
                Sight::make('name', 'Имя')->render(function (Contact $contact) {
                    return $contact->user->name;
                }),
                Sight::make('email', 'Email')->render(function (Contact $contact) {
                    return $contact->user->email;
                }),
                Sight::make('title', 'Тема'),
                Sight::make('message', 'Сообщение'),
                Sight::make('created_at', 'Создано')->render(function (Contact $contact) {
                    return $contact->created_at->diffForHumans()
                        . ' (' . $contact->created_at->format('d.m.Y H:i') . ')';
                }),
            ])
        ];
    }
}
