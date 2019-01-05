{{-- regular object attribute --}}
@php
	$value = data_get($entry, $column['name']);

	if (is_array($value)) {
		$value = json_encode($value);
	}
@endphp

{{-- email link --}}
<span><a href="mailto:{{ $entry->{$column['name']} }}">{{ str_limit(strip_tags($value), array_key_exists('limit', $column) ? $column['limit'] : 50, "[...]") }}</a></span>
