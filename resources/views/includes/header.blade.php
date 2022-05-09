<header>
    <nav>
        <div class="nav_wrap">
            <div class="menu_block">
                <label class="menuToggle" for="menuCheck" class="link">Меню</label>
                <input id="menuCheck" type="checkbox" />
                <ul class="menu clearfix">
                    <li id="logo">
                        <a href="{{ route('blog') }}" class="link">{{ config('app.name') }}
                            <div class="sub_logo">
                                {{ __('Мама, я блогер :)') }}
                                {{-- {{ __('Без "воды"') }} --}}
                                {{-- "цитаты великих людей?" --}}
                            </div>
                        </a>
                    </li>
                    <li><a href="https://github.com/chasovnikov" class="link">GitHub</a></li>
                    <li><a href="{{ route('contact') }}" class="link">Контакты</a></li>

                    @if (! Auth::guest() && isAdmin())

                    <li>
                        <a href="{{ route('adminIndex') }}" class="link">
                            <span>Админ-панель</span>
                        </a>
                    </li>

                    @endif

                </ul>
            </div>
        </div>
        <hr />
    </nav>
</header>
