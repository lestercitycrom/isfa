@props([
	'name',
	'class' => 'h-4 w-4',
])

@php
	$n = (string) $name;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center '.$class]) }} aria-hidden="true">
	@if($n === 'eye')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
			<circle cx="12" cy="12" r="3"/>
		</svg>
	@elseif($n === 'pencil')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M12 20h9"/>
			<path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
		</svg>
	@elseif($n === 'plus')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M12 5v14"/>
			<path d="M5 12h14"/>
		</svg>
	@elseif($n === 'filter')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M22 3H2l8 9v7l4 2v-9l8-9z"/>
		</svg>
	@elseif($n === 'refresh')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M21 12a9 9 0 0 1-15.3 6.3"/>
			<path d="M3 12a9 9 0 0 1 15.3-6.3"/>
			<path d="M21 3v6h-6"/>
			<path d="M3 21v-6h6"/>
		</svg>
	@elseif($n === 'search')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<circle cx="11" cy="11" r="8"/>
			<path d="M21 21l-4.3-4.3"/>
		</svg>
	@elseif($n === 'users')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
			<circle cx="9" cy="7" r="4"/>
			<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
			<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
		</svg>
	@elseif($n === 'database')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<ellipse cx="12" cy="5" rx="9" ry="3"/>
			<path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
			<path d="M3 12c0 1.66 4.03 3 9 3s9-1.34 9-3"/>
		</svg>
	@elseif($n === 'upload')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
			<path d="M17 8l-5-5-5 5"/>
			<path d="M12 3v12"/>
		</svg>
	@elseif($n === 'download')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
			<path d="M7 10l5 5 5-5"/>
			<path d="M12 15V3"/>
		</svg>
	@elseif($n === 'box')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M3 7l9 5 9-5"/>
			<path d="M3 7v10l9 5 9-5V7"/>
			<path d="M12 12v10"/>
		</svg>
	@elseif($n === 'tag')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M20.5 13.5l-7 7a2 2 0 0 1-2.8 0L3 12.8V3h9.8l7.7 7.7a2 2 0 0 1 0 2.8z"/>
			<circle cx="7.5" cy="7.5" r="1.5"/>
		</svg>
	@elseif($n === 'truck')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M3 7h11v10H3z"/>
			<path d="M14 9h4l3 3v5h-7z"/>
			<circle cx="7" cy="19" r="2"/>
			<circle cx="18" cy="19" r="2"/>
		</svg>
	@elseif($n === 'file-text')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
			<path d="M14 2v6h6"/>
			<path d="M8 13h8"/>
			<path d="M8 17h8"/>
			<path d="M8 9h2"/>
		</svg>
	@elseif($n === 'activity')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M3 12h4l2-5 4 10 2-5h6"/>
		</svg>
	@elseif($n === 'image')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<rect x="3" y="3" width="18" height="18" rx="2"/>
			<circle cx="8.5" cy="8.5" r="1.5"/>
			<path d="M21 15l-5-5L5 21"/>
		</svg>
	@elseif($n === 'chevron-down')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M6 9l6 6 6-6"/>
		</svg>
	@elseif($n === 'list')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M8 6h13"/>
			<path d="M8 12h13"/>
			<path d="M8 18h13"/>
			<path d="M3 6h.01"/>
			<path d="M3 12h.01"/>
			<path d="M3 18h.01"/>
		</svg>
	@elseif($n === 'settings')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7z"/>
			<path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 0 1-1.4 3.4h-.2a1.7 1.7 0 0 0-1.6 1.1 2 2 0 0 1-3.7 0 1.7 1.7 0 0 0-1.6-1.1H10a2 2 0 0 1-1.4-3.4l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H7a2 2 0 0 1 0-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1A2 2 0 0 1 10 3.6h.2a1.7 1.7 0 0 0 1.6-1.1 2 2 0 0 1 3.7 0 1.7 1.7 0 0 0 1.6 1.1h.2A2 2 0 0 1 21.4 6l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.5 1H23a2 2 0 0 1 0 4h-.2a1.7 1.7 0 0 0-1.5 1z"/>
		</svg>
	@elseif($n === 'trash')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M3 6h18"/>
			<path d="M8 6V4h8v2"/>
			<path d="M19 6l-1 14H6L5 6"/>
			<path d="M10 11v6"/>
			<path d="M14 11v6"/>
		</svg>
	@elseif($n === 'building')
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M3 21h18"/>
			<path d="M5 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16"/>
			<path d="M9 7h.01"/>
			<path d="M9 11h.01"/>
			<path d="M9 15h.01"/>
			<path d="M13 7h.01"/>
			<path d="M13 11h.01"/>
			<path d="M13 15h.01"/>
			<path d="M17 21V9a2 2 0 0 1 2-2h1v14"/>
		</svg>
	@else
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M12 20h9"/>
			<path d="M12 4h9"/>
			<path d="M4 9h16"/>
			<path d="M4 15h16"/>
		</svg>
	@endif
</span>
