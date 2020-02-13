{{-- single relationships (1-1, 1-n) --}}

<span>
    @php($attributes = $crud->getModelAttributeFromRelation($entry, $column['entity'], $column['attribute']))


        @if(count($attributes))

            @php($text = e(str_limit(strip_tags(implode(', ', $attributes)), array_key_exists('limit', $column) ? $column['limit'] : 40, '[...]')))

            @if(isset($column['anchor']['href']))
                @include('crud::inc.column_anchors',['text' => $text])
            @else
                {{ $text }}
            @endif
        @else
            -
        @endif
</span>
