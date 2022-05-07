<div class="category_block">
    <h3>Категории</h2>
        <nav class="category_list">
            <ul>
                @if (!empty($categories))

                @foreach($categories as $category)

                @unless (empty($articleCount = $category->articles->count()))

                <li class="category_link">
                    <a href="{{ route('showByCategory', ['id' => $category->id] ) }}">
                        {{ $category->title }}
                    </a>
                </li>

                @endunless

                @endforeach

                @endif
            </ul>
        </nav>
</div>

<hr />

<div class="category_block">
    <h3>Последние статьи</h2>
        <nav class="category_list">
            <ul>
                @if (!empty($recents[0]))

                @foreach($recents as $recent)

                <li class="category_link">
                    <a href="{{ route('articleShow', ['id' => $recent->id]) }}">
                        {{ $recent->title }}
                    </a>
                </li>

                @endforeach

                @endif
            </ul>
        </nav>
</div>

<hr />

<div class="category_block">
    <h3>Поиск по тегам</h2>
        <nav class="category_list tags_list">

            @if (!empty($tags[0]))

            @foreach($tags as $tag)

            <button class="tag">
                <a href="{{ route('showByTag', ['id' => $tag->id]) }}">
                    {{ $tag->title }}
                </a>
            </button>

            @endforeach

            @endif
        </nav>
</div>
