<!-- number input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

    <div class="input-group">
        @if(isset($field['prefix'])) <div class="input-group-addon">{{ $field['prefix'] }}</div> @endif
        <input
        	type="number"
        	name="{{ $field['name'] }}"
            value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
            @include('crud::inc.field_attributes')
        	>
        @if(isset($field['suffix'])) <div class="input-group-addon">{{ $field['suffix'] }}</div> @endif
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>