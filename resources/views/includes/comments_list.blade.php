<div class="comments">
    <a name="comments"></a>

    @if($article->comments->isNotEmpty())
    <h3>{{ __('Комментарии') }} ({{ $article->comments->count() }})</h3>

    @foreach($article->comments as $comment)
    <div>
        <div class="response-text-left ">
            <a name="comment{{ $comment->id }}"></a>

            <div class="comment_user">{{$comment->name}}:</div class="comment_user">
            <br>
            <div class="publication_date">
                <time>{{ $comment->created_at->diffForHumans() }}</time>
            </div>
        </div>

        <div class="response-text-right">
            <p class="" data-wow-delay=".5s">
                {{$comment->content}}
            </p>

            @auth
            @if (Auth::user()->hasAccess('platform.index') || Auth::user()->id == $comment->user_id)
            <form action="{{ route('commentDelete', $comment) }}" method="post">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('Удалить') }}</button>
            </form>
            @endif
            @endauth

            <br>
            <hr>
        </div>
        <div class="clearfix"></div>
    </div>

    @endforeach
    @endif
</div>
