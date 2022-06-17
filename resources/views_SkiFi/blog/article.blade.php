@extends('layouts.base')

@section('content')

@unless (empty($article))

<div class="card">
    <!-- <img src="..." class="card-img-top" alt="..."> -->
    <div class="card-body">
        <h4 class="card-title text-center mt-2 mb-4">{{ $article->title }}</h4>

        <span class="text-end">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-date" viewBox="0 0 16 16">
                <path d="M6.445 11.688V6.354h-.633A12.6 12.6 0 0 0 4.5 7.16v.695c.375-.257.969-.62 1.258-.777h.012v4.61h.675zm1.188-1.305c.047.64.594 1.406 1.703 1.406 1.258 0 2-1.066 2-2.871 0-1.934-.781-2.668-1.953-2.668-.926 0-1.797.672-1.797 1.809 0 1.16.824 1.77 1.676 1.77.746 0 1.23-.376 1.383-.79h.027c-.004 1.316-.461 2.164-1.305 2.164-.664 0-1.008-.45-1.05-.82h-.684zm2.953-2.317c0 .696-.559 1.18-1.184 1.18-.601 0-1.144-.383-1.144-1.2 0-.823.582-1.21 1.168-1.21.633 0 1.16.398 1.16 1.23z" />
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
            </svg>
            {{ $article->published_at  }}
        </span>

        &nbsp;

        @foreach($article->tags as $tag)
        <a href="{{ route('showByTag', ['id' => $tag->id]) }}" class="no_underline">
            <span class="badge bg-primary rounded-pill">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="1em" height="1em" viewBox="0 0 32 32" class="me-2" role="img" fill="currentColor" componentname="orchid-icon">
                    <path d="M31.999 13.008l-0-10.574c0-1.342-1.092-2.434-2.433-2.434h-10.793c-0.677 0-1.703 0-2.372 0.67l-15.81 15.811c-0.38 0.38-0.59 0.884-0.59 1.421 0 0.538 0.209 1.043 0.589 1.423l12.088 12.085c0.379 0.381 0.883 0.59 1.421 0.59s1.042-0.209 1.421-0.589l15.811-15.812c0.678-0.677 0.674-1.65 0.67-2.591zM29.915 14.186l-15.826 15.811-12.086-12.101 15.794-15.797c0.159-0.099 0.732-0.099 0.968-0.099l0.45 0.002 10.35-0.002c0.239 0 0.433 0.195 0.433 0.434v10.582c0.002 0.38 0.004 1.017-0.084 1.169zM24 4c-2.209 0-4 1.791-4 4s1.791 4 4 4c2.209 0 4-1.791 4-4s-1.791-4-4-4zM24 10c-1.105 0-2-0.896-2-2s0.895-2 2-2 2 0.896 2 2-0.895 2-2 2z">
                    </path>
                </svg>
                {{ $tag->title }}
            </span>
        </a>
        @endforeach

        <p class="card-text mt-3">
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
        <a href="#" class="card-link    ">Card link</a>
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
