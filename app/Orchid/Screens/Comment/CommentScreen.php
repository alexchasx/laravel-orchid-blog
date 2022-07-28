<?php

namespace App\Orchid\Screens\Comment;

use App\Models\Comment;
use Carbon\Carbon;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Sight;

class CommentScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query($id): iterable
    {
        return [
            'comment' => Comment::findOrFail($id),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'CommentScreen';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::legend('comment', [
                Sight::make('id'),

                Sight::make('article_id', 'Статья')
                    ->render(function (Comment $comment) {
                        return '[ID=' . $comment->article->id . '] ' . $comment->article->title;
                }),
                Sight::make('name'),
                Sight::make('content')->render(function (Comment $comment) {
                    return $comment->content;
                }),
                Sight::make('created_at')->render(function (Comment $comment) {
                    $carbon = Carbon::create($comment->created_at);

                    return $carbon->format('d.m.Y');
                }),
            ])
        ];
    }
}
