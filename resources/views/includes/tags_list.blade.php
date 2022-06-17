@if (!empty($rubrics))
<!-- <section id="search" class="category_block">
    <form role="search" method="get" class="search-form" action="{{ route('search') }}">
        <label for="search" class="block text-sm font-medium text-gray-700">
            {{-- <span class="screen-reader-text">{{ __('Поиск для:') }}</span> --}}
            <input type="search" class="search-field" placeholder=" {{ __('Поиск по сайту') }} …" value="" name="query">
        </label>
        <button type="submit" class="submit">
            <span class="screen-reader-text">{{ __('Поиск') }}</span>
        </button>
    </form>
</section> -->
@endif

@if (!empty($tags[0]))
<div class="d-flex flex-wrap align-items-center justify-content-center">

    <!-- <h3 class="border">{{ __('Поиск по меткам') }}</h2> -->

    <a href="{{ route('home') }}" class="knopka01">

        {{ __('Все') }}
        <span class="tag_count">(150)</span>
    </a>

    @foreach($tags as $tag)
    @unless (empty($articleCount = $tag->articles->count()))
    <a href="{{ route('showByTag', ['id' => $tag->id]) }}" class="knopka01">

        {{ $tag->title }}
        <!-- <span class="badge bg-primary rounded-pill">
            ({{ $articleCount }})
        </span> -->
        <span class="tag_count">(45)</span>
    </a>
    @endunless
    @endforeach
</div>
@endif
