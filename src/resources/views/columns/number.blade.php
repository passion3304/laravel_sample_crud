{{-- regular object attribute --}}
<td>{{ (array_key_exists('prefix', $column) ? $column['prefix'] : '').number_format($entry->{$column['name']}).(array_key_exists('suffix', $column) ? $column['suffix'] : '') }}</td>
