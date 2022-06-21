@extends('layouts.app')

@section('content')

@unless (empty($article))

<div class="article_card">
    <h3> @if (! Auth::guest() && isAdmin())
        [ID={{ $article->id }}]
        @endif

        {{ $article->title }}
    </h3>

    <div class="publication_date">{{ $article->published_at }}</div>

    @if (! Auth::guest() && isAdmin())
    <button class="tag">
        <a href="{{ route('articleEdit',['id'=>$article->id]) }}">
            {{ __('Редактировать') }}
        </a>
    </button>
    @endif

    <p>{!! $article->content_html !!}</p>
</div>
<br />

@include('blog/comments/_list')

<hr>

{{-- @include('blog/comments/_form') --}}

@endunless

@push('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
@endpush

@endsection
