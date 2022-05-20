<?php

namespace App\Http\Controllers\Blog;

use App\Models\Blog\Article;
use App\Models\Blog\Rubric;
use App\Models\Blog\Tag;

class ContactController extends BaseController
{
    public function show()
    {
        return view('blog.contact', [
            'rubrics' => Rubric::notEmpties(),
            'tags' => Tag::notEmpties(),
        ]);
    }
}
