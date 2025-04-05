@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="pagination-nav">
    <div class="pagination-wrapper">
        {{-- Previous Page Link --}}
        <div class="pagination-prev">
            @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="Previous">
                <span class="pagination-button pagination-button-disabled" aria-hidden="true">
                    &laquo; Previous
                </span>
            </span>
            @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-button" aria-label="Previous">
                &laquo; Previous
            </a>
            @endif
        </div>

        {{-- Pagination Elements --}}
        <div class="pagination-numbers">
            <div class="pagination-numbers-container">
                @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                <span aria-disabled="true">
                    <span class="pagination-button pagination-button-disabled">{{ $element }}</span>
                </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <span aria-current="page">
                    <span class="pagination-button pagination-button-active">{{ $page }}</span>
                </span>
                @else
                <a href="{{ $url }}" class="pagination-button" aria-label="Go to page {{ $page }}">
                    {{ $page }}
                </a>
                @endif
                @endforeach
                @endif
                @endforeach
            </div>
        </div>

        {{-- Next Page Link --}}
        <div class="pagination-next">
            @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-button" aria-label="Next">
                Next &raquo;
            </a>
            @else
            <span aria-disabled="true" aria-label="Next">
                <span class="pagination-button pagination-button-disabled" aria-hidden="true">
                    Next &raquo;
                </span>
            </span>
            @endif
        </div>
    </div>
</nav>
@endif