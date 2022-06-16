<header class="p-3 text-white border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">
                <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap">
                    <use xlink:href="#bootstrap"></use>
                </svg>
            </a>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="{{ route('home') }}" class="nav-link px-2 text-primary">ChasDev</a></li>
                <li><a href="https://github.com/chasovnikov" class="nav-link px-2 text-white">GitHub</a></li>
                <li><a href="{{ route('contact') }}" class="nav-link px-2 text-white">Контакты</a></li>

                @if (isAdmin())
                <li>
                    <a href="{{ route('platform.main') }}" class="border rounded nav-link px-2 text-white">
                        <span>{{ __('Админ-панель') }}</span>
                    </a>
                </li>
                @endif
            </ul>

            <form class="col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" role="search">
                <input type="search" class="form-control text-white bg-dark" placeholder="Search..." aria-label="Search">
            </form>

            <div class="text-end">
                @if (Auth::guest())
                <button type="button" class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Войти</button>
                <button type=" button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Регистрация</button>
                @else
                <a href="{{ route('logout') }}" class="nav-link px-2 border rounded text-white" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    {{ __('Выход') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
                @endif
            </div>

        </div>
    </div>
</header>
