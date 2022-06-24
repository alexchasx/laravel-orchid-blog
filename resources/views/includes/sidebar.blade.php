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

@if (!empty($rubrics))
<hr />
<section id="categories" class="category_block">
    <h3>{{ __('Рубрики') }}</h3>
        <nav class="category_list">
            <ul>
                @foreach($rubrics as $rubric)

                {{-- @unless (empty($articleCount = $rubrics->articles->count()))
                <li class="category_link">
                    <a href="{{ route('showByRubric', ['id' => $rubrics->id] ) }}">
                {{ $rubrics->title }}
                </a>
                </li>
                @endunless --}}

                @if ($rubric->exists)
                <li class="category_link">
                    <a href="{{ route('showByRubric', $rubric->id ) }}">
                        {{ $rubric->title }}
                    </a>
                </li>
                @endif

                @endforeach
            </ul>
        </nav>
</section>
@endif

{{-- @if (!empty($recents[0]))
<hr />
<section id="recent" class="category_block">
    <h3>{{ __('Последние статьи') }}</h2>
<nav class="category_list">
    <ul>
        @foreach($recents as $recent)

        <li class="category_link">
            <a href="{{ route('articleShow', ['id' => $recent->id]) }}">
                {{ $recent->title }}
            </a>
        </li>

        @endforeach
    </ul>
</nav>
</section>
@endif --}}

@if (!empty($tags[0]))
<hr />
<section id="tags" class="category_block">
    <h3>{{ __('Поиск по меткам') }}</h3>
        <nav class="category_list tags_list">
            @foreach($tags as $tag)

            @unless (empty($articleCount = $tag->articles->count()))
            {{-- @unless (empty($tag->articles)) --}}
            <button class="tag">
                <a href="{{ route('showByTag', ['id' => $tag->id]) }}">
                    {{ $tag->title }}
                </a>
            </button>
            @endunless

            @endforeach
        </nav>
</section>
@endif
