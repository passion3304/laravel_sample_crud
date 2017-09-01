<td>
  @if( !empty($entry->{$column['src']}) )
    <a
      href="{{ $entry->{$column['src']} }}"
      target="_blank"
    >
      <img
        src="{{ $entry->{$column['src']} }}"
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
