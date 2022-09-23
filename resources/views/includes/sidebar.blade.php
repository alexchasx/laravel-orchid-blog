@if (!empty($rubrics)) {{-- Чтобы не отображалось на /login и /register --}}
<section id="search" class="category_block">
    <form role="search" method="GET" class="search-form" action="{{ route('home') }}">
        <label>
            <input type="search" class="search-field" placeholder=" {{ __('Поиск по статьям') }} …" value="" name="search">
        </label>
        <button type="submit" class="search_submit">
            <span class="screen-reader-text">{{ __('Найти') }}</span>
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
                <a href="{{ route('showByRubric', $rubric ) }}" class="link">
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
    <h3 class="tags_link_title">{{ __('Метки (тэги)') }}</h3>
    <nav class="category_list">
        @foreach($tags as $tag)

        @if ($tag->exists)
        <a class="link tags_link" href="{{ route('showByTag', $tag) }}" style="font-size: {{ $tag->popular ?: 16 }}px">
            {{ $tag->title }}
        </a>
        @endif

        @endforeach
    </nav>
</section>
@endif

{{-- <section id="donate">
    <hr />
    <a href="#" class="link">{{ __('Поддержать проект') }}</a>
</section> --}}
