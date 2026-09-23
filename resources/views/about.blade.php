@extends('layouts.techlog')

@section('content')

<section class="article container reveal">
    <div class="article-head">
        <a class="back" href="{{ route('home') }}">← На главную</a>
    </div>

    <div class="prose">
        <p>{{ config('app.name') }} — независимый блог о том, как создавать и поддерживать программные системы. Здесь важны контекст, проверяемость и цена каждого технического решения.</p>
        <p>Материалы написаны для разработчиков, тимлидов и инженеров, которым нужно не просто узнать «как», а понять «почему».</p>

        <div class="callout">
            <p><strong>Хотите пообщаться?</strong> Напишите нам через <b><a href="{{ route('contact') }}">форму обратной связи</a></b> или подпишитесь на рассылку новых статей.</p>
        </div>
    </div>
</section>

@endsection
