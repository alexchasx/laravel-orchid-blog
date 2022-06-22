<?php

namespace App\Http\Controllers;

use App\Models\Rubric;
use App\Models\Tag;

class ContactController extends ParentController
{
    public function show()
    {
        return view('contact', [
            'rubrics' => Rubric::notEmpties(),
            'tags' => Tag::notEmpties(),
        ]);
    }
}
