@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center w-100" role="navigation" aria-label="Navegación de páginas">
        <ul class="pagination mb-0 d-flex justify-content-between w-100">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link d-inline-flex align-items-center gap-1">
                        <i class="bi bi-chevron-left" style="font-size: 0.8rem;"></i> Anterior
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link d-inline-flex align-items-center gap-1" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bi bi-chevron-left" style="font-size: 0.8rem;"></i> Anterior
                    </a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link d-inline-flex align-items-center gap-1" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Siguiente <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link d-inline-flex align-items-center gap-1">
                        Siguiente <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
