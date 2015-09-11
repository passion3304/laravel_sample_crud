<!-- html5 datetime input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
        type="datetime-local"
        class="form-control"

        @foreach ($field as $attribute => $value)
            @if ($attribute == 'value')
                value="{{ strftime('%Y-%m-%dT%H:%M:%S', strtotime($value)) }}"
            @else
                {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
        >
  </div>