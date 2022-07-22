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
                Sight::make('name'),
                Sight::make('email'),
                Sight::make('title'),
                Sight::make('message')->render(function (Contact $contact) {
                    return $contact->message;
                }),
                Sight::make('created_at'),
            ])
        ];
    }
}
