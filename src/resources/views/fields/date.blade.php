<!-- html5 date input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
        type="date"
        class="form-control"

        @foreach ($field as $attribute => $value)
            @if ($attribute=='value')
                value="{{ $field['value']->toDateString() }}"
            @else
                {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
        >
  </div>