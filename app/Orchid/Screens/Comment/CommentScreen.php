<?php

namespace App\Orchid\Screens\Comment;

use App\Models\Article;
use App\Models\Comment;
use Carbon\Carbon;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Sight;
use Orchid\Support\Facades\Alert;
use Orchid\Screen\Actions\Link;
use Illuminate\Support\Str;

class CommentScreen extends Screen
{
    /**
     * @var Comment
     */
    public $comment;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Comment $comment): iterable
    {
        return [
            'comment' => $comment,
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Просмотр комментария';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Удалить')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->comment->exists),
        ];
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
                        $strTitle = '[ID=' . $comment->article->id . '] '
                            . $comment->article->title;

                        return Link::make($strTitle)
                            ->route('articleShow', $comment->article->id);
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

    /**
     * @param Comment $comment
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function remove(Comment $comment)
    {
        $id = $comment->id;

        $comment->delete();

        Alert::info('Комментарий c ID='. $id .' успешно удалён.');

        return redirect()->route('platform.comment.list');
    }
}
