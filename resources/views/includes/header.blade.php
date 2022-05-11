<header>
    <nav>
        <div class="nav_wrap">
            <div class="menu_block">
                <label class="menuToggle" for="menuCheck" class="link">Меню</label>
                <input id="menuCheck" type="checkbox" />
                <ul class="menu clearfix">
                    <li id="logo" class="left">

                        <a href="{{ route('blog') }}" class="link">{{ config('app.name') }}
                            <div class="sub_logo">
                                {{ __('Мама, я блогер :)') }}
                                {{-- {{ __('Без "воды"') }} --}}
                                {{-- "цитаты великих людей?" --}}
                            </div>
                        </a>
                    </li>
                    <li class="left"><a href="https://github.com/chasovnikov" class="link">GitHub</a></li>

                    <li class="left"><a href="{{ route('contact') }}" class="link">Контакты</a></li>


                    @if (! Auth::guest())
                    @if (isAdmin())

                    <li class="right">
                        <a href="{{ route('platform.main') }}" class="link">
                            <span>Админ-панель</span>
                        </a>
                    </li>

                    @endif

                    <li class="right">
                        <a href="{{ route('logout') }}" class="link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Выход') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>

                    @else

                    <li class="right">
                        <a href="{{ route('login') }}" class="link login">
                            <span>Войти</span>
                        </a>
                    </li>
                    <li class="right">
                        <a href="{{ route('register') }}" class="link login">
                            <span>Зарегистрироваться</span>
                        </a>
                    </li>

                    @endif

                </ul>
            </div>
        </div>
        <hr />
    </nav>
</header>
