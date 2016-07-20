<!-- html5 datetime input -->
<div @include('crud::inc.fieldWrapperAttributes') >
    <label>{{ $field['label'] }}</label>
    <input
        type="datetime-local"
        name="{{ $field['name'] }}"
        value="{{ strftime('%Y-%m-%dT%H:%M:%S', strtotime(old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )))) }}"
        @include('crud::inc.fieldAttributes')
        >

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>