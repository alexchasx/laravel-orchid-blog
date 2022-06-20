<!-- <div class="codrops-top clearfix">
    <span class="right"><a href="http://www.sitehere.ru/nabor-effektov-pri-navedenii-css" title="Вернуться к статье"><span>Вернуться к статье</span></a></span>
</div> -->
<header class="codrops-header">
    <div>
        <a href="/">
            <div class="logo">IT-Инженер <span class="pick">записки веб-разработчика</span></div>
        </a>
    </div>

    <nav class="nav_list">
        <a href="#" class="button_01">
            GitHub
        </a>
        <a href="#" class="button_01">
            Контакты
        </a>
    </nav>

    <div class="search">
        <form action="{{ route('search') }}" method="GET" role="search">
            <input type="search" class="button11" placeholder="Поиск..." aria-label="Search">
            <button type="submit">Поиск</button>
        </form>
    </div>

    <!-- <form action="#" method="GET" class="search_2" role="form">
        <div>
            <input type="text" name="query" class="form-control" size="35" placeholder="Поиск">
            <span class="input-group-btn">
                <button class="button-search" type="submit">
                    <span class="glyphicon glyphicon-search"></span>
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" class="svg-inline--fa fa-search fa-w-16 text-indigo-500" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z">
                        </path>
                    </svg>
                </button>
            </span>
        </div>
    </form> -->

    @include('includes.tags_list')
</header>
