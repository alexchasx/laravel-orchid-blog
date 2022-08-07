<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Http\Request;

class ContactController extends MainController
{
    public function index()
    {
        return view('contact', [
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => Tag::articlePublished()->get(),
            'meta_title' => 'Обратная связь',
            'meta_desc' => 'Обратная связь',
        ]);
    }

    public function store(Request $request)
    {
        Contact::create(
            $this->validate($request, [
                'name' => ['required', 'string', 'max:250'],
                'email' => ['required', 'string', 'max:250', 'email'],
                'title' => ['string', 'max:5000'],
                'message' => ['required', 'max:500000'],
        ], [
            'name.required' => 'Это поле необходимо для заполнения',
            'name.max' => 'Количество символов в поле не может превышать 250',
            'email.required' => 'Это поле необходимо для заполнения',
            'email.max' => 'Количество символов в поле не может превышать 250',
            'email.string' => 'Почта должно быть строкой',
            'email.email' => 'Ваша почта должна соответствовать формату mail@some.domain',
            'title.string' => 'В поле должна быть строка',
            'title.max' => 'Слишком длинная строка',
            'message.max' => 'Слишком длинное сообщение',
        ]));

        // return back()->with(['success' => 'Сообщение оправлено.']);
        return view('contact', [
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => Tag::articlePublished()->get(),
            'success' => 'Сообщение оправлено.',
        ]);
    }
}
