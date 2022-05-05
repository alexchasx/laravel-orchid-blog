<div class="border-bottom pb-3 mb-4">
    @isset($link)
    <div class="mb-2 ms-2 text-start">
        {{ $link }}
    </div>
    @endisset

    <div class="d-flex justify-content-between">
        <div>
            <h1 class="h2 mb-0">
                {{ $slot }}
            </h1>
        </div>

        @isset($right)
        <div>
            {{ $right }}
        </div>
        @endisset
    </div>
</div>

<x-errors />
