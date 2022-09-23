<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('includes.meta_tags')

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>

<body>
    <div class="main_block">

        @if($alert = session()->pull('alert'))
        <div class="alert alert-{{ session()->pull('type') }} mb-0 rounded-0 text-center small py-2">
            {{ $alert }}
            <br>
            Алерт
        </div>
        @endif

        @if (!empty($message))
        <div class="alert alert-danger text-center">
            {{$message}}
        </div>
        @endif

        <header>
            <nav>
                <div class="nav_wrap">
                    <div class="menu_block">
                        <label class="menuToggle" for="menuCheck" class="link">{{ config('app.name') }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ __('Меню') }}<span class="caret"></span></label>
                        <input id="menuCheck" type="checkbox" />
                        <ul class="menu clearfix">
                            <li id="logo" class="left">
                                    <img src="/storage/avatar.png" alt="logo">
                                <a href="{{ route('home') }}" class="link">{{ config('app.name') }}
                                    <div class="sub_logo">{{ config('my_config.sub_logo') }}</div>
                                </a>
                            </li>
                            <li class="left"><a href="https://github.com/alexchasx" class="link">{{ __('GitHub') }}</a></li>

                            <li class="left"><a href="{{ route('contact') }}" class="link">{{ __('Обратная связь') }}</a></li>

                            <!-- @include('includes/locale_links') -->

                            @auth
                            <li class="right">
                                <a href="{{ route('logout') }}" class="link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Выход') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>

                            <li class="right">
                                <a href="#" class="user_name">
                                    <span>{{ Auth::user()->name }}</span>
                                </a>
                            </li>

                            @hasAccess('platform.index')
                            <li class="right">
                                <a href="{{ route('platform.articles') }}" class="link">
                                    <span>{{ __('Админ-панель') }}</span>
                                </a>
                            </li>
                            @endhasAccess

                            @else

                            <li class="right">
                                <a href="{{ route('register') }}" class="link login">
                                    <span>{{ __('Регистрация') }}</span>
                                </a>
                            </li>

                            <li class="right">
                                <a href="{{ route('login') }}" class="link login">
                                    <span>{{ __('Вход') }}</span>
                                </a>
                            </li>

                            @endauth

                        </ul>
                    </div>
                </div>
                <hr />
            </nav>
        </header>

        <div class="{{ isset($rubrics) ? 'wrap' : '' }}">

            <main class="left_block">

                @if ($metaTitle)
                <h1 class="pageTitle">{{ $metaTitle }}</h1>
                @endif

                @yield('content')

            </main>

            @if (isset($rubrics))
            <aside class="right_block">
                @include('includes.sidebar')
            </aside>
            @endif

        </div>

        @if (isset($rubrics))
        <footer>
            <hr />
            <div class="footer">
                <div class="made_in">{{ __('Сделано на ') }} <a href="https://laravel.com/" class="link">Laravel</a></div>

                <div class="made_in">&#169; 2022
                    @unless(Date('Y') == 2022)
                    {{ '- ' . Date('Y') }}
                    @endunless
                    <a href="{{ route('home') }}" class="link">{{ config('app.name') }}</a>
                </div>
            </div>
        </footer>
        @endif

    </div>
</body>

@stack('js')

<script src="{{ asset('js/app.js') }}"></script>

</html>
