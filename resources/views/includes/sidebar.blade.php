@if (!empty($rubrics)) {{-- Чтобы не отображалось на /login и /register --}}
<section id="search" class="category_block">
    <form role="search" method="GET" class="search-form" action="{{ route('home') }}">
        <label>
            {{-- <span class="screen-reader-text">{{ __('Поиск для:') }}</span> --}}
            <input type="search" class="search-field" placeholder=" {{ __('Поиск по сайту') }} …" value="" name="search">
        </label>
        <button type="submit" class="submit">
            <span class="screen-reader-text">{{ __('Поиск') }}</span>
        </button>
    </form>
</section>
@endif

@if ($rubrics->isNotEmpty())
<hr />
<section id="categories" class="category_block">
    <h3>{{ __('Рубрики') }}</h3>
    <nav class="category_list">
        <ul>
            @foreach($rubrics as $rubric)

            @if ($rubric->exists)
            <li class="category_link">
                <a href="{{ route('showByRubric', $rubric->id ) }}" class="link">
                    {{ $rubric->title }}
                </a>
            </li>
            @endif

            @endforeach

            @auth

            @hasAccess('platform.index')
            <li class="category_link">
                <a href="{{ route('notpublic') }}" class="link">
                    <span>{{ __('Неопубликованные') }}</span>
                </a>
            </li>
            @endhasAccess

            @endauth
        </ul>
    </nav>
</section>
@endif

@if ($tags->isNotEmpty())
<hr />
<section id="tags" class="category_block">
    <h3>{{ __('Метки (тэги)') }}</h3>
    <nav class="category_list tags_list">
        @foreach($tags as $tag)

        @if ($rubric->exists)
        <button class="tag">
            <a href="{{ route('showByTag', $tag) }}">
                {{ $tag->title }}
            </a>
        </button>
        @endif

        @endforeach
    </nav>
</section>
<hr />
@endif

<section id="donate">
    <a href="#" class="link">{{ __('Поддержать проект') }}</a>
</section>
