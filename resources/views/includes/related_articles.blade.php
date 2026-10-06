@if(($relatedArticles ?? collect())->isNotEmpty())
<div class="section container">
    <div class="section-head reveal">
        <div>
            <h2>Похожие статьи</h2>
        </div>
    </div>

    <div class="related-grid">
        @foreach($relatedArticles as $related)
            <article class="post reveal">
                @if($related->image)
                    <div class="post-image">
                        <img src="{{ Storage::url($related->image) }}" alt="{{ $related->title }}" loading="lazy">
                    </div>
                @endif
                <div class="post-body">
                    <div class="meta">
                        {{ \Carbon\Carbon::parse($related->published_at)->locale('ru')->isoFormat('D MMM YYYY') }} · {{ $related->reading_minutes }} МИН
                    </div>
                    <h3><a href="{{ route('articleShow', ['article' => $related->slug]) }}" class="article_title">{{ $related->title }}</a></h3>
                    <p>{{ Str::limit($related->excerpt ?? '', 150) }}</p>
                    <a class="read" href="{{ route('articleShow', ['article' => $related->slug]) }}">
                        Читать <span>↗</span>
                    </a>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endif
