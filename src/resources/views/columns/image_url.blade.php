<td>
  @if( !empty($entry->{$column['name']}) ) 
    <a 
      href="{{ $entry->{$column['name']} }}"
      target="_blank"
    >
      <img 
        src="{{ $entry->{$column['name']} }}" 
        style="
          height: {{ isset($column['height']) ? $column['height'] : "25px" }};
          width: {{ isset($column['width']) ? $column['width'] : "auto" }};
          border-radius: 3px;"
      />
    </a>
  @else
    -
  @endif
</td>
