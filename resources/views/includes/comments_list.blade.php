@if($article->comments->isNotEmpty())
    <div class="section-head reveal">
        <div>
            <p class="eyebrow">DISCUSSION / {{ $article->comments->count() }}</p>
            <h2>Комментарии</h2>
        </div>
    </div>

    <div class="comments-list">
        @foreach($article->comments as $comment)
            <article class="comment reveal" id="comment{{ $comment->id }}">
                <div class="comment-head">
                    <strong class="comment-author">{{ $comment->name }}</strong>
                    <time class="comment-date" datetime="{{ $comment->created_at->toIso8601String() }}">
                        {{ \Carbon\Carbon::parse($comment->created_at)->locale('ru')->isoFormat('D MMM YYYY · HH:mm') }}
                    </time>
                </div>

                <p class="comment-text">{{ $comment->content }}</p>

                @auth
                    @if (Auth::user()->hasAccess('platform.index') || Auth::user()->id == $comment->user_id)
                        <form action="{{ route('commentDelete', $comment) }}" method="post" class="comment-delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button">Удалить</button>
                        </form>
                    @endif
                @endauth
            </article>
        @endforeach
    </div>
@endif
