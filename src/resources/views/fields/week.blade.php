<!-- html5 week input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
        type="week"
        class="form-control"

        @foreach ($field as $attribute => $value)
            @if (is_string($attribute) && is_string($value))
                {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
        >
  </div>