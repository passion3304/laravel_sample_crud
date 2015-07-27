<!-- html5 range input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
        type="range"
        class="form-control"

        @foreach ($field as $attribute => $value)
            {{ $attribute }}="{{ $value }}"
        @endforeach
        >
  </div>