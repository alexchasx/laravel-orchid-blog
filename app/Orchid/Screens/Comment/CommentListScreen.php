<?php

namespace App\Orchid\Screens\Comment;

use App\Models\Comment;
use Carbon\Carbon;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Str;

class CommentListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'comments' => Comment::filters()->defaultSort('id', 'desc')
                ->paginate(50),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Комментарии';
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
            Layout::table('comments', [

                TD::make('id', 'ID')->sort(true),

                TD::make('Смотреть')->render(function (Comment $comment) {
                    return Link::make('')
                        ->route('platform.comment', $comment)
                        ->icon('eye');
                }),

                // TD::make('read', 'Прочитано')->render(function (comment $comment) {
                //     return CheckBox::make('commnets[]')
                //         ->value($comment->read);
                // })->sort(),

                TD::make('name', 'Имя')
                    ->render(function (Comment $comment) {
                        return Str::limit($comment->name, 40);
                }),

                TD::make('content', 'Контент')
                    ->render(function (Comment $comment) {
                        return Str::limit($comment->content, 50);
                }),

                TD::make('article_id', 'Статья')
                    ->render(function (Comment $comment) {
                        $strTitle = '[ID=' . $comment->article->id . '] ' . $comment->article->title;

                        return Link::make( Str::limit($strTitle, 30) )
                            ->route('articleShow', $comment->article->id);
                    }),

                // TD::make('email', 'Email')->defaultHidden(),

                TD::make('created_at', 'Дата создания')->render(function (Comment $comment) {
                    $carbon = Carbon::create($comment->created_at);
                    return $carbon->format('d.m.Y');
                }),

                // TD::make('Удалить')
                //     ->render(function (Comment $comment) {
                //         return Link::make('')
                //             ->method('deleteComment')
                //             ->asyncParameters([
                //                 'comment' => $comment
                //             ])
                //             ->icon('trash');
                //     }),
            ]),
        ];
    }

    // public function deleteComment(Comment $comment)
    // {
    //     dd($comment->toArray());

    //     (new CommentScreen())->remove($comment);
    // }
}
