@extends('layouts.base')

@section('title', 'Главная')

@section('content')

@if (!empty($articles[0]))


<div class="flex-1">
    <div style="opacity: 1; transform: translateY(0px); transition: all 125ms ease-in-out 0s;">
        <main>
            <div class="container px-3 py-16 mx-auto max-w-3xl">
                <h1>Articles</h1>
                <p class="font-light text-gray-700 text-sm mb-12">Ты найдешь здесь все статьи, которые я опубликовал,
                    приятного чтения !</p>
                <ul>
                    @foreach ($articles as $article)
                    <li class="mb-8"><a class="btn btn--lightest btn--sm" href="/categories">Billets</a>
                        <h2 class="font-semibold mb-0 mt-2 leading-tight">
                            <a class="text-black" hreflang="en" href="/installing-pi-hole-on-a-headless-raspberry-pi-zero-w-from-scratch">
                                <span>{{ $article->title }}</span>
                            </a>
                        </h2>
                        <div class="text-gray-700 text-sm">
                            <span class="font-light" content="2021-07-16">
                                {{ $article->published_at }}
                            </span>
                        </div>
                    </li>
                    @endforeach

                </ul>

                <div class="flex justify-center items-baseline"><span class="mx-4"><svg viewBox="0 0 17 13" class="w-4 text-gray-700 stroke-current">
                            <g stroke-width="1" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <g transform="translate(1.000000, 0.000000)">
                                    <path d="M0,5.8325 L15,5.8325"></path>
                                    <polyline points="5.83333333 0 0 5.83333333 5.83333333 11.6666667"></polyline>
                                </g>
                            </g>
                        </svg></span>
                    <div class="mx-4 flex"><a aria-current="page" class="mx-1 rounded-full h-10 w-10 flex items-center justify-center bg-indigo-100 text-indigo-500 font-semibold" href="/articles">1</a><a class="mx-1 rounded-full h-10 w-10 flex items-center justify-center text-gray-700 hover:bg-indigo-100 hover:no-underline" href="/articles/page/2">2</a></div><a class="mx-4" rel="prenextv" aria-label="Page suivante" href="/articles/page/2"><svg viewBox="0 0 17 13" class="w-4 text-gray-700 hover:text-indigo-500 stroke-current">
                            <g stroke-width="1" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <g transform="translate(1.000000, 0.000000)">
                                    <g transform="translate(7.500000, 6.000000) scale(-1, 1) translate(-7.500000, -6.000000) ">
                                        <path d="M0,5.8325 L15,5.8325"></path>
                                        <polyline points="5.83333333 0 0 5.83333333 5.83333333 11.6666667"></polyline>
                                    </g>
                                </g>
                            </g>
                        </svg></a>
                </div>
            </div>
        </main>
    </div>
</div>

@else
<div class="article_card">
    {{ __('Ничего не нашлось') }}
</div>
@endif

@endsection
