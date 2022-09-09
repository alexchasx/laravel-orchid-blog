<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ContactController extends MainController
{
    public function index()
    {
        return view('contact',
            $this->getResponseArray(builder: null, metaTitle: __('Обратная связь')));
    }

    public function store(ContactRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();        
        $success = $user->saveContact($request->only('title', 'message'));
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
