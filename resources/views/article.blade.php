@extends('layouts.base')

@section('content')

@unless (empty($article))

<article class="article_card">
    <h3> @if (! Auth::guest())
        [ID={{ $article->id }}]
        @endif

        {{ $article->title }}
    </h3>

    @include('includes/publication_date')

    <p>{!! $article->content_raw !!}</p>

    <br>
</article>
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
