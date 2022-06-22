<?php

namespace App\Http\Controllers;

use App\Models\Rubric;
use App\Models\Tag;

class ContactController extends ParentController
{
    public function __invoke()
    {
        return view('contact', [
            'rubrics' => Rubric::articlePublished()->get(),
            'tags' => Tag::articlePublished()->get(),
        ]);
    }
}
