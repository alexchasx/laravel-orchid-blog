<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContactController extends MainController
{
    public function index(): View
    {
        return $this->service->getResponse(
            metaTitle: __('Обратная связь'),
            view: 'contact',
        );
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $messageType = 'error' ;
        $message = __('Сообщение не получилось отправить. Что-то сломалось.');
        /** @var User $user */
        $user = Auth::user();
        if ($user->saveContact($request->only('title', 'message'))) {
            $messageType = 'success' ;
            $message = __('Сообщение отправлено!');
        }
        
        return redirect()
            ->route('contact')
            ->with($messageType, $message);
    }
}
