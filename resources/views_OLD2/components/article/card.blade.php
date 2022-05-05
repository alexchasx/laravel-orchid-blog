<x-card>
    <x-card-body>
        <div class="h6">
            <h2 class="h5">
                <a href="{{ route('articleShow', $article->id) }}">{{ $article->title }}</a>
            </h2>

            <div>
                {{ now()->format('d.m.Y H:i:s') }}
            </div>
        </div>
    </x-card-body>
</x-card>
