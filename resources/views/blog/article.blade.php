@extends('layouts.base')

@section('content')

@unless (empty($article))

<div class="card">
    <img src="..." class="card-img-top" alt="...">
    <div class="card-body">
        <h5 class="card-title">{{ $article->title }}</h5>
        <p class="card-text">
            {!! $article->content_html !!}
        </p>

        @if (! Auth::guest() && isAdmin())
        <button class="tag">
            <a href="{{ route('articleEdit',['id'=>$article->id]) }}">
                {{ __('Редактировать') }}
            </a>
        </button>
        @endif
    </div>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">An item</li>
        <li class="list-group-item">A second item</li>
        <li class="list-group-item">A third item</li>
    </ul>
    <div class="card-body">
        <a href="#" class="card-link">Card link</a>
        <a href="#" class="card-link">Another link</a>
    </div>
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
