{{-- enumerate the values in an array  --}}
@php
    $json = @json_decode($entry['attributes'][$column['name']]);
    $displayValue = '-';
    if( $json ){

        // Concatinate them all
        if( isset($column['list']) && $column['list'] ){
            $list = [];
            foreach($json as $j){
                if( isset( $j->{$column['display_field']} ) ){
                    $list[] = $j->{$column['display_field']};
                }
            }
            $displayValue = implode(', ', $list);
        }

        //Count them all
        else {
            $displayValue = count($json) . ' items';
        }
    }
@endphp
<td>
    {{ $displayValue }}
</td>
