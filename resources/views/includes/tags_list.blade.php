@if (!empty($tags[0]))
<nav class="">

    @empty($currentTagId)
    @php($currentTagId = 0)
    @endempty

    <a href="{{ route('home') }}" class="knopka01 @if($currentTagId == 'all') active @endif">

        {{ __('Все') }}
    </a>

    @foreach($tags as $tag)
    @unless (empty($articleCount = $tag->articles->count()))
    <a href="{{ route('showByTag', ['id' => $tag->id]) }}" class="knopka01 @if($currentTagId == $tag->id) active @endif">

        {{ $tag->title }}
    </a>
    @endunless
    @endforeach
</nav>
@endif
