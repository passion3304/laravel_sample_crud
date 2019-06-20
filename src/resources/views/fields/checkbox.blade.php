<!-- checkbox field -->

<div @include('crud::inc.field_wrapper_attributes') >
    @include('crud::inc.field_translatable_icon')
    <div class="form-check">
    	  <input type="hidden" name="{{ $field['name'] }}" value="0">
    	  <input type="checkbox" class="form-check-input" value="1"

          name="{{ $field['name'] }}"
          id="{{ $field['name'] }}_checkbox"

          @if (old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false)
                 checked="checked"
          @endif

          @if (isset($field['attributes']))
              @foreach ($field['attributes'] as $attribute => $value)
    			{{ $attribute }}="{{ $value }}"
        	  @endforeach
          @endif
          >
        <label class="form-check-label font-weight-normal" for="{{ $field['name'] }}_checkbox">{!! $field['label'] !!}</label>

        {{-- HINT --}}
        @if (isset($field['hint']))
            <p class="help-block">{!! $field['hint'] !!}</p>
        @endif
    </div>
</div>
