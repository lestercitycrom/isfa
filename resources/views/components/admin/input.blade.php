@props([
	'label' => null,
	'hint' => null,
	'error' => null,
	'size' => 'md', // md|sm
	'variant' => 'default', // default|filter
	'type' => 'text',
])

@php
	$pad = $size === 'sm' ? 'px-3 py-2 text-sm' : 'px-3 py-2.5 text-sm';
	$base = 'w-full rounded-xl border bg-white text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2';

	$variantClass = $variant === 'filter'
		? 'border-slate-200 focus:border-slate-400 focus:ring-slate-200'
		: 'border-slate-200 focus:border-slate-400 focus:ring-slate-200';

	$isTextarea = $type === 'textarea';
	$tag = $isTextarea ? 'textarea' : 'input';
@endphp

<div class="space-y-1">
	@if($label)
		<label class="text-xs font-semibold text-slate-700">{{ $label }}</label>
	@endif

	@if($isTextarea)
		<textarea {{ $attributes->except('type')->merge(['class' => $base.' '.$pad.' '.$variantClass.' min-h-[100px]']) }}>{{ $attributes->get('value') ?? $slot }}</textarea>
	@else
		<input type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$pad.' '.$variantClass]) }}>
	@endif

	@if($hint)
		<div class="text-xs text-slate-500">{{ $hint }}</div>
	@endif

	@if($error)
		<div class="text-xs font-medium text-rose-600">{{ $error }}</div>
	@endif
</div>
