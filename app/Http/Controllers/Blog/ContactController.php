<?php

namespace App\Http\Controllers\Blog;

use App\Models\Blog\Article;
use App\Models\Blog\Tag;

class ContactController extends BaseController
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
