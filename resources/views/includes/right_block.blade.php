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

@if (!empty($rubrics))
<div class="list-group">

    @foreach($rubrics as $rubric)
    @if ($rubric->exists)
    <a href="{{ route('showByRubric', ['id' => $rubric->id] ) }}" class="rubric-link list-group-item list-group-item-action d-flex justify-content-between align-items-center
        @if($currentRubric && $rubric->id == $currentRubric->id) active @endif" aria-current="true">
        {{ $rubric->title }}

        <span class="badge bg-primary rounded-pill">5</span>
    </a>
    @endif
    @endforeach

</div>

@endif

@if (!empty($tags[0]))
<hr />
<section id="tags" class="category_block">
    <h3>{{ __('Поиск по меткам') }}</h2>
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
