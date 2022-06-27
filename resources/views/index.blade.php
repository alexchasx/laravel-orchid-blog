@extends('layouts.base')

@section('content')

{{-- {{ memory_get_usage() }} --}}

@if (!empty($articles[0]))

@foreach ($articles as $article)

<div class="article_card">
    <h3><a href="{{route('articleShow', $article->id)}}" class="continue_read">

            @if (! Auth::guest())
            [ID={{ $article->id }}]
            @endif

            {{ $article->title }}
        </a>
    </h3>

    <div class="publication_date">
        <span>{{ $article->published_at }}</span>
        @foreach ($article->tags as $tag)
        &nbsp;&nbsp;<span class="tag_title">
            <a href="{{ route('showByTag', $tag->id) }}">{{ $tag->title }}</a>
        </span>
        @endforeach
    </div>

    {{-- <p>{!! Str::limit($article->content_raw, 200) !!}</p>

    <a href="{{route('articleShow', $article->id)}}" class="continue_read">
    {{ __('Читать далее') }}
    </a>
    --}}
</div>

@unless ($loop->last)
{{-- не показывать последнюю полосу --}}
<br />
<hr />
<br />
@endunless

@endforeach

@else
<div class="article_card">
    {{ __('Ничего не нашлось') }}
</div>
@endif
<nav>
    <ul class="pagination">
        {{ $articles->links('vendor.pagination.default') }}
    </ul>
</nav>

@endsection
