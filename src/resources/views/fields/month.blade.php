<!-- html5 month input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
        type="month"
        class="form-control"

        @foreach ($field as $attribute => $value)
            {{ $attribute }}="{{ $value }}"
        @endforeach
        >
  </div>