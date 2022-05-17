{{-- @if (true) --}}
<section id="search" class="category_block">
    <form role="search" method="get" class="search-form" action="{{ route('search') }}">
        <label>
            {{-- <span class="screen-reader-text">{{ __('Поиск для:') }}</span> --}}
            <input type="search" class="search-field" placeholder="{{-- {{ __('Поиск') }} --}} …" value="" name="query">
        </label>
        <button type="submit" class="search-submit">
            <span class="screen-reader-text">{{ __('Поиск') }}</span>
        </button>
    </form>
</section>
{{-- @endif --}}

@if (!empty($categories))
<hr />
<section id="categories" class="category_block">
    <h3>{{ __('Рубрики') }}</h2>
        <nav class="category_list">
            <ul>
                @foreach($categories as $category)

                @unless (empty($articleCount = $category->articles->count()))
                <li class="category_link">
                    <a href="{{ route('showByCategory', ['id' => $category->id] ) }}">
                        {{ $category->title }}
                    </a>
                </li>
                @endunless

                @endforeach
            </ul>
        </nav>
</section>
@endif

@if (!empty($recents[0]))
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
@endif

@if (!empty($tags[0]))
<hr />
<section id="tags" class="category_block">
    <h3>{{ __('Поиск по меткам') }}</h2>
        <nav class="category_list tags_list">
            @foreach($tags as $tag)

            @unless (empty($articleCount = $tag->articles->count()))
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
