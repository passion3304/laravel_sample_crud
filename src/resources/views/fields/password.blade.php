<!-- password -->
<div @include('crud::inc.fieldWrapperAttributes') >
    <label>{{ $field['label'] }}</label>
    <input
    	type="password"
    	name="{{ $field['name'] }}"
        @include('crud::inc.fieldAttributes')
    	>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>