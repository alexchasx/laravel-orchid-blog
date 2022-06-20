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
<nav class="">

    <!-- <h3 class="border">{{ __('Поиск по меткам') }}</h2> -->

    @empty($currentTagId)
    @php($currentTagId = 0)
    @endempty

    <a href="{{ route('home') }}" class="knopka01 @if($currentTagId == 'all') active @endif">

        {{ __('Все') }}
        <!-- <span style="position: absolute;" class="tag_count">(150)</span> -->
    </a>

    @foreach($tags as $tag)
    @unless (empty($articleCount = $tag->articles->count()))
    <a href="{{ route('showByTag', ['id' => $tag->id]) }}" class="knopka01 @if($currentTagId == $tag->id) active @endif">

        {{ $tag->title }}
        <!-- <span class="badge bg-primary rounded-pill">
            ({{ $articleCount }})
        </span> -->
        <!-- <span style="position: absolute;" class="tag_count">(45)</span> -->
    </a>
    @endunless
    @endforeach
</nav>
@endif
