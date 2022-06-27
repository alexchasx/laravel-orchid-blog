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
        </ul>
    </nav>
</section>
@endif

@if ($tags->isNotEmpty())
<hr />
<section id="tags" class="category_block">
    <h3>{{ __('Поиск по меткам') }}</h3>
    <nav class="category_list tags_list">
        @foreach($tags as $tag)

        @if ($rubric->exists)
        <button class="tag">
            <a href="{{ route('showByTag', ['id' => $tag->id]) }}">
                {{ $tag->title }}
            </a>
        </button>
        @endif

        @endforeach
    </nav>
</section>
@endif
