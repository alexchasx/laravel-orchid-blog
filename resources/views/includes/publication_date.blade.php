<div class="publication_date">
    <time>{{ $article->published_at }}</time>
    @foreach ($article->tags as $tag)
    &nbsp;&nbsp;<em class="tag_title">
        <!-- <a href="{{ route('showByTag', $tag->id) }}"></a> -->
        {{ $tag->title }}
    </em>
    @endforeach
</div>
