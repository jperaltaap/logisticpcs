@if ($paginator->hasPages())
    <nav class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 w-100 py-1" role="navigation" aria-label="Navegación de páginas">
        {{-- Counter Info --}}
        <div class="small text-muted text-center text-sm-start">
            Mostrando 
            <span class="fw-semibold text-heading">{{ $paginator->firstItem() ?? 0 }}</span>
            a
            <span class="fw-semibold text-heading">{{ $paginator->lastItem() ?? 0 }}</span>
            de
            <span class="fw-semibold text-heading">{{ $paginator->total() }}</span>
            registros
        </div>

        {{-- Page Navigation Links --}}
        <ul class="pagination mb-0 d-inline-flex align-items-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">
                        <i class="bi bi-chevron-left" style="font-size: 0.8rem;"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="bi bi-chevron-left" style="font-size: 0.8rem;"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">
                        <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
