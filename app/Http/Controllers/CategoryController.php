<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Показывает статьи по категории
     *
     * @param $categoryId
     *
     * @return $this
     */
    // public function showByCategory($categoryId)
    public function index($categoryId)
    {
        $category = (new Category())->find($categoryId)->first(['title']);

        $articles = $this->allArticles(null, $categoryId);

        return view('index')->with([
            // 'articles' => $articles->paginate(self::PAGINATE),
            // 'category' => $category,
            // 'popular' => $this->popularArticles($articles),
            // 'recent' => $this->recentArticles($articles),
            // 'categories' => $this->showCategories(),
            // 'tags' => $this->showTags(),
            // 'empty' => self::EMPTY_IMAGE,
        ]);
    }
}
