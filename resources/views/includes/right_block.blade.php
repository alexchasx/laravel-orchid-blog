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

<br>

@if (!empty($tags[0]))
<div class="mb-4">
    <!-- <h3 class="border">{{ __('Поиск по меткам') }}</h2> -->

    @foreach($tags as $tag)
    @unless (empty($articleCount = $tag->articles->count()))
    <a href="{{ route('showByTag', ['id' => $tag->id]) }}">
        <span class="badge bg-primary rounded-pill">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="1em" height="1em" viewBox="0 0 32 32"
                class="me-2" role="img" fill="currentColor" componentname="orchid-icon">
                <path
                    d="M31.999 13.008l-0-10.574c0-1.342-1.092-2.434-2.433-2.434h-10.793c-0.677 0-1.703 0-2.372 0.67l-15.81 15.811c-0.38 0.38-0.59 0.884-0.59 1.421 0 0.538 0.209 1.043 0.589 1.423l12.088 12.085c0.379 0.381 0.883 0.59 1.421 0.59s1.042-0.209 1.421-0.589l15.811-15.812c0.678-0.677 0.674-1.65 0.67-2.591zM29.915 14.186l-15.826 15.811-12.086-12.101 15.794-15.797c0.159-0.099 0.732-0.099 0.968-0.099l0.45 0.002 10.35-0.002c0.239 0 0.433 0.195 0.433 0.434v10.582c0.002 0.38 0.004 1.017-0.084 1.169zM24 4c-2.209 0-4 1.791-4 4s1.791 4 4 4c2.209 0 4-1.791 4-4s-1.791-4-4-4zM24 10c-1.105 0-2-0.896-2-2s0.895-2 2-2 2 0.896 2 2-0.895 2-2 2z">
                </path>
            </svg>
            {{ $tag->title }}
        </span>
    </a>
    @endunless
    @endforeach
</div>
@endif
