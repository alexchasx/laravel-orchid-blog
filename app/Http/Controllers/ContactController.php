<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class ContactController extends ParentController
{
    public function show()
    {
        $builder = Article::allPublished();
        $recents = Article::recents($builder);
        // $article->viewed += 1;
        // $article->save();

        // dd($article->comments());
        // $comments = Article::find($articleId)->comments;

        return view('contact')->with([
            'recents' => $recents,
            'categories' => Category::allPublished(),
            'tags' => Tag::allActive(),
        ]);
    }
}
