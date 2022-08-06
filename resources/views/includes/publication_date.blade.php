<div class="publication_date">

    @auth
        @hasAccess('platform.index')
        <span class="article_id">
            [ID={{ $article->id }}]
        </span>
        @endhasAccess
    @endauth

    <time>{{ $article->published_at->diffForHumans() }}</time>
    @foreach ($article->tags as $tag)
    &nbsp;&nbsp;<em class="tag_title">
        <!-- <a href="{{ route('showByTag', $tag) }}"></a> -->
        {{ $tag->title }}
    </em>
    @endforeach
</div>
