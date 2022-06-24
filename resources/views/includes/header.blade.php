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

                    @if (Auth::user()->hasAccess('platform.*'))
                    <li class="right">
                        <a href="{{ route('platform.main') }}" class="link">
                            <span>{{ __('Админ-панель') }}</span>
                        </a>
                    </li>
                    @endif

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
