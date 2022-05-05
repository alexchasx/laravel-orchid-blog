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
                                ПЕРСОНАЛЬНЫЙ БЛОГ
                            </div>
                        </a>
                    </li>
                    <li><a href="" class="link">GitHub</a></li>
                    <li><a href="" class="link">Контакты</a></li>
                </ul>
            </div>
        </div>
        <hr />
    </nav>
</header>
