@if ($paginator->hasPages())
	<nav role="navigation" aria-label="{{ __('common.pagination') }}" class="flex items-center justify-between gap-3">
		<div class="text-xs text-slate-500">
			@if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
				{{ __('common.pagination_showing') }}
				<span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
				{{ __('common.pagination_to') }}
				<span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
				{{ __('common.pagination_of') }}
				<span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
			@endif
		</div>

		<div class="flex items-center gap-1">
			{{-- Previous --}}
			@if ($paginator->onFirstPage())
				<span class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white text-slate-400 cursor-not-allowed">
					{{ __('common.pagination_prev') }}
				</span>
			@else
				@php
					$prevUrl = $paginator->previousPageUrl();
					$prevUrl = is_string($prevUrl) ? preg_replace('#/admin/admin/#', '/admin/', $prevUrl) : $prevUrl;
				@endphp
				<a href="{{ $prevUrl }}" rel="prev"
					class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white text-slate-900 hover:bg-slate-50">
					{{ __('common.pagination_prev') }}
				</a>
			@endif

			{{-- Elements --}}
			@foreach ($elements as $element)
				@if (is_string($element))
					<span class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white text-slate-400">
						{{ $element }}
					</span>
				@endif

				@if (is_array($element))
					@foreach ($element as $page => $url)
						@php
							$fixedUrl = is_string($url) ? preg_replace('#/admin/admin/#', '/admin/', $url) : $url;
						@endphp
						@if ($page == $paginator->currentPage())
							<span aria-current="page"
								class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold bg-slate-900 text-white">
								{{ $page }}
							</span>
						@else
							<a href="{{ $fixedUrl }}"
								class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white text-slate-900 hover:bg-slate-50"
								aria-label="{{ __('common.pagination_go_to_page', ['page' => $page]) }}">
								{{ $page }}
							</a>
						@endif
					@endforeach
				@endif
			@endforeach

			{{-- Next --}}
			@if ($paginator->hasMorePages())
				@php
					$nextUrl = $paginator->nextPageUrl();
					$nextUrl = is_string($nextUrl) ? preg_replace('#/admin/admin/#', '/admin/', $nextUrl) : $nextUrl;
				@endphp
				<a href="{{ $nextUrl }}" rel="next"
					class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white text-slate-900 hover:bg-slate-50">
					{{ __('common.pagination_next') }}
				</a>
			@else
				<span class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold border border-slate-200 bg-white text-slate-400 cursor-not-allowed">
					{{ __('common.pagination_next') }}
				</span>
			@endif
		</div>
	</nav>
@endif
