{{-- closure function column type --}}
<span>
    @php($text = $column['function']($entry))
        @if(isset($column['anchor']['href']))
            @include('crud::inc.column_anchors',['text' => $text])
        @else
            {!! $text !!}
        @endif

</span>
