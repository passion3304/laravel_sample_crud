{{-- enumerate the values in an array  --}}
@php
    $array = data_get($entry, $column['name']);
@endphp

<span>
    <?php
    $suffix = isset($column['suffix']) ? $column['suffix'] : 'items';

    // the value should be an array wether or not attribute casting is used
    if (! is_array($array)) {
        $array = json_decode($array, true);
    }
    ?>
    @if($array && count($array))

        @php($text = count($array).' '.$suffix)

        @if(isset($column['anchor']['href']))
            @include('crud::inc.column_anchors',['text' => $text])
        @else
            {{ $text }}
        @endif
    @else
        -
    @endif
</span>
