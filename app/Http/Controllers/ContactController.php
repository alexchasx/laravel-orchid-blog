<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;

class ContactController extends ParentController
{
    public function show()
    {
        $builder = Article::allPublished();
        $recents = Article::recents($builder);

        return view('contact')->with([
            'recents' => $recents,
            'categories' => Category::allPublished(),
            'tags' => Tag::allActive(),
        ]);
    }
}
