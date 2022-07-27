@extends('layouts.base')

@section('content')

{{-- {{ memory_get_usage() }} --}}

@if (!empty($articles[0]))

@foreach ($articles as $article)

<article class="article_card">
    <h3 class="article_title"><a href="{{route('articleShow', $article->id)}}" class="continue_read">
            {{ $article->title }}
        </a>
    </h3>

    @include('includes/publication_date')

    {{-- <p>{!! Str::limit($article->content_raw, 200) !!}</p>

    <a href="{{route('articleShow', $article->id)}}" class="continue_read">
    {{ __('Читать далее') }}
    </a>
    --}}
</article>

@unless ($loop->last)
{{-- не показывать последнюю полосу --}}
<br />
<hr class="hr-lines" />
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
