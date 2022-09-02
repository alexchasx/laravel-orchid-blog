<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends MainController
{
    public function index(): View | Factory
    {
        return view('contact',
            $this->getResponseArray(builder: null, metaTitle: 'Обратная связь')
        );
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $success = Contact::create($request->only([
            'name',
            'email',
            'title',
            'message',
        ]));

        if ($success) {
            return redirect()
                ->route('contact')
                ->with('success', 'Сообщение отправлено!');
        }
        return redirect()
            ->route('contact')
            ->with('error', 'Сообщение не получилось отправить. Что-то сломалось.');
    }
}
