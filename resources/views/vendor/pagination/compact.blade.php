@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">
        <p class="hidden text-xs text-slate-500 sm:block">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <div class="inline-flex items-center">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center rounded-l-md border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm text-slate-400 cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center rounded-l-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="border-y border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-500">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="border-y border-slate-200 bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-900">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="border-y border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center rounded-r-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center rounded-r-md border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm text-slate-400 cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif
