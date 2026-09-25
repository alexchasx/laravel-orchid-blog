@extends('layouts.techlog')

@section('content')

<section class="contact container">
    {{-- Contact Intro --}}
    <div class="contact-intro reveal">
        <h1>Давайте обсудим<br><em>технологии</em></h1>
        <p>Есть тема для статьи, идея сотрудничества или нашли неточность? Напишите — читаем каждое сообщение</p>
        @if(config('my_config.contact_email') || config('my_config.my_github'))
            {{-- Контакты берутся из .env (CONTACT_EMAIL, MY_GITHUB), см. config/my_config.php --}}
            <div class="contact-links">
                @if(config('my_config.contact_email'))
                    <a href="mailto:{{ config('my_config.contact_email') }}">{{ config('my_config.contact_email') }}</a>
                @endif

                @if(config('my_config.my_github'))
                    <a href="{{ config('my_config.my_github') }}" target="_blank" rel="noopener">{{ preg_replace('#^https?://#', '', config('my_config.my_github')) }}</a>
                @endif
            </div>
        @endif
    </div>

    {{-- Contact Form --}}
    <form class="contact-form reveal" data-contact-form action="{{ route('contact.store') }}" method="POST">
        @csrf

        @auth
            <input type="hidden" name="name" value="{{ auth()->user()->name }}">
            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
        @else
            <label for="name">
                Имя
                <input id="name" name="name" type="text" autocomplete="name" placeholder="Как к вам обращаться" required minlength="2">
            </label>

            <label for="contact-email">
                Email
                <input id="contact-email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required>
            </label>
        @endauth

        <label for="message">
            <div style="margin-bottom: 8px;">Сообщение</div>
            <textarea id="message" name="message" rows="7" placeholder="Расскажите, чем можем помочь…" required minlength="10"></textarea>
        </label>

        <button class="button primary" type="submit">Отправить сообщение →</button>
        <p class="form-message" aria-live="polite"></p>
    </form>
</section>

@endsection
