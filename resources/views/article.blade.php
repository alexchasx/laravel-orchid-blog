@extends('layouts.base')

@section('content')

@unless (empty($article))

<div class="article_card">
    <h3> @if (! Auth::guest())
        [ID={{ $article->id }}]
        @endif

        {{ $article->title }}</h3>

    <div class="publication_date">{{ $article->published_at }}</div>

    {{-- @if (! Auth::guest()) --}}
    {{-- <button class="tag">
        <a href="{{ route('articleEdit',['id'=>$article->id]) }}">
    {{ __('Редактировать') }}
    </a>
    </button> --}}
    {{-- @endif --}}

    <pre>
        <!-- <p>{!! $article->content_raw !!}</p> -->
    </pre>

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
