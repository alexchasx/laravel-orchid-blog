@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Пагинация">
        @if ($paginator->total() > 0)
            <p class="pagination-info">
                Показано {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} из {{ $paginator->total() }} материалов
            </p>
        @endif

        <ul class="pagination-list">
            {{-- Ссылка «Назад» --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Назад">
                    <span class="page-link" aria-hidden="true">←</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Назад">←</a>
                </li>
            @endif

            {{-- Элементы пагинации --}}
            @foreach ($elements as $element)
                {{-- Разделитель «…» --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Массив ссылок на страницы --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Ссылка «Вперёд» --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Вперёд">→</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Вперёд">
                    <span class="page-link" aria-hidden="true">→</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
