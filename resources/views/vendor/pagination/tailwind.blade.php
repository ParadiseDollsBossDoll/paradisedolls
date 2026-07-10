@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pd-pagination">
        <div class="flex items-center justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="pd-pagination-mobile-link pd-pagination-disabled" aria-disabled="true">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pd-pagination-mobile-link">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pd-pagination-mobile-link">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="pd-pagination-mobile-link pd-pagination-disabled" aria-disabled="true">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden items-center justify-end sm:flex">
            <span class="pd-pagination-group" aria-label="{{ __('Pagination pages') }}">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span class="pd-pagination-link pd-pagination-arrow pd-pagination-disabled" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18 9 12l6-6" />
                            </svg>
                        </span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pd-pagination-link pd-pagination-arrow" aria-label="{{ __('pagination.previous') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18 9 12l6-6" />
                        </svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span class="pd-pagination-link pd-pagination-dots">{{ $element }}</span>
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="pd-pagination-link pd-pagination-current">{{ $page }}</span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="pd-pagination-link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pd-pagination-link pd-pagination-arrow" aria-label="{{ __('pagination.next') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span class="pd-pagination-link pd-pagination-arrow pd-pagination-disabled" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </span>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
