{{-- closure function column type --}}
<span>
    @php($text = $column['function']($entry))

    @include('crud::inc.column_anchors',['text' => $text, 'escaped' => false])

</span>
