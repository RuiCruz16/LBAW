@if ($paginator->hasPages())
    <nav class="custom-pagination-nav">
        <ul class="custom-pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="custom-pagination-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="custom-pagination-link" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li class="custom-pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="custom-pagination-link" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="custom-pagination-item disabled" aria-disabled="true"><span class="custom-pagination-dots">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="custom-pagination-item active" aria-current="page"><span class="custom-pagination-link active">{{ $page }}</span></li>
                        @else
                            <li class="custom-pagination-item"><a href="{{ $url }}" class="custom-pagination-link">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="custom-pagination-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="custom-pagination-link" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class="custom-pagination-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="custom-pagination-link" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
