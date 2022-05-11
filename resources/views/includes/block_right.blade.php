@if (!empty($categories))
<div class="category_block">
    <h3>Категории</h2>
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
</div>
@endif


@if (!empty($recents[0]))
<hr />
<div class="category_block">
    <h3>Последние статьи</h2>
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
</div>
@endif

@if (!empty($tags[0]))
<hr />
<div class="category_block">
    <h3>Поиск по тегам</h2>
        <nav class="category_list tags_list">
            @foreach($tags as $tag)

            <button class="tag">
                <a href="{{ route('showByTag', ['id' => $tag->id]) }}">
                    {{ $tag->title }}
                </a>
            </button>

            @endforeach
        </nav>
</div>
@endif
