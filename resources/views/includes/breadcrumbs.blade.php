{{--
    Хлебные крошки (Breadcrumb).
    Выводится только если массив непустой — на главной и служебных страницах
    крошек нет.
--}}

@if(!empty($breadcrumbs))
<nav class="breadcrumbs container" aria-label="Навигация">
    <ol>
        @foreach($breadcrumbs as $index => $item)
            <li>
                @if($item['url'] !== null && $index < count($breadcrumbs) - 1)
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
