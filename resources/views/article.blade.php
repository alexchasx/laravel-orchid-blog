@extends('layouts.base')

@section('content')

@unless (empty($article))

<div class="article_card">
    <h3> @if (! Auth::guest())
        [ID={{ $article->id }}]
        @endif

        {{ $article->title }}
    </h3>

    <div class="publication_date">
        <span>{{ $article->published_at }}</span>
        @foreach ($article->tags as $tag)
        &nbsp;&nbsp;<span class="tag_title">
            <a href="{{ route('showByTag', $tag->id) }}">{{ $tag->title }}</a>
        </span>
        @endforeach
    </div>

    <p>{!! $article->content_raw !!}</p>

    <br>
</div>
<br />

@include('includes/comments_list')

<hr>

@include('includes/comments_form')

@endunless

@push('scripts')
<script type="text/javascript">
</script>
@endpush

@endsection
