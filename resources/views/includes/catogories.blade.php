<div class="category_block">
    <h3>Последние статьи</h2>
        <nav class="category_list">
            <ul>
                @foreach($recents as $recent)

                <li class="category_link">
                    <a href="">{{ $recent->title }}</a>
                </li>
                @endforeach
            </ul>
        </nav>
</div>
<hr />

<div class="category_block">
    <h3>Категории</h2>
        <nav class="category_list">
            <ul>
                @foreach($categories as $category)
                <li class="category_link">
                    <a href="">{{ $category->title }}</a>
                </li>
                @endforeach
            </ul>
        </nav>
</div>
