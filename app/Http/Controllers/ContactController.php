<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;

class ContactController extends MainController
{
    public function index()
    {
        return view('contact',
            $this->getResponseArray(builder: null, metaTitle: __('Обратная связь')));
    }

    public function store(ContactRequest $request)
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
