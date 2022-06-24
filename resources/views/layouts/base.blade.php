<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page.title', config('app.name'))</title>
    <link rel="icon" href="favicon.ico"><!-- 32×32 -->
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
                        <label class="menuToggle" for="menuCheck" class="link">{{ __('Меню') }}</label>
                        <input id="menuCheck" type="checkbox" />
                        <ul class="menu clearfix">
                            <li id="logo" class="left">
                                <a href="{{ route('home') }}" class="link">{{ config('app.name') }}
                                    {{-- <div class="sub_logo">{{ __('персональный блог') }}</div> --}}
                                    <div class="sub_logo">{{ __('блокнот веб-разработчика') }}</div>
                                </a>
                            </li>
                            <li class="left"><a href="https://github.com/chasovnikov" class="link">{{ __('GitHub') }}</a></li>

                            <li class="left"><a href="{{ route('contact') }}" class="link">{{ __('Контакты') }}</a></li>

                            @if (! Auth::guest())
                                <li class="right">
                                    <a href="{{ route('logout') }}" class="link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Выход') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>

                                @hasAccess('platform.index')
                                <li class="right">
                                    <a href="{{ route('platform.main') }}" class="link">
                                        <span>{{ __('Админ-панель') }}</span>
                                    </a>
                                </li>
                                @endhasAccess

                            @else
                                <li class="right">
                                    <a href="{{ route('login') }}" class="link login">
                                        <span>{{ __('Войти') }}</span>
                                    </a>
                                </li>
                                <li class="right">
                                    <a href="{{ route('register') }}" class="link login">
                                        <span>{{ __('Зарегистрироваться') }}</span>
                                    </a>
                                </li>

                            @endif

                        </ul>
                    </div>
                </div>
                <hr />
            </nav>
        </header>

        <section>
            <div class="wrap">

                <main class="left_block">
                    @yield('content')
                </main>

                <aside class="right_block">
                    @include('includes.sidebar')
                </aside>

            </div>
        </section>

        <footer>
            <hr />
            <div class="footer">
                <a href="{{ route('home') }}" class="link">{{ config('app.name') }}</a>

                <div class="made_in">{{ __('Сделано на Laravel') }}</div>

                <div class="made_in">2022
                    @unless(Date('Y') == 2022)
                        {{ '- ' . Date('Y') }}
                    @endunless
                </div>
            </div>
        </footer>

    </div>
</body>

@stack('js')

<script src="{{ asset('js/app.js') }}"></script>

</html>
