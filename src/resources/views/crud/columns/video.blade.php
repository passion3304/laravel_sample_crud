{{-- regular object attribute --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if( !empty($column['value']) ) {

        // if attribute casting is used, convert to object
        if (is_array($column['value'])) {
            $column['value'] = (object)$column['value'];
        } elseif (is_string($column['value'])) {
            $column['value'] = json_decode($column['value']);
        }

        $bgColor = $column['value']->provider === 'vimeo' ? '#00ADEF' : '#DA2724';
    }
@endphp

<span>
    @if( isset($column['value']) )
    <a target="_blank" href="{{$column['value']->url}}" title="{{$column['value']->title}}" style="background: {{$bgColor}}; color: #fff; display: inline-block; width: 30px; height: 25px; text-align: center; border-top-left-radius: 3px; border-bottom-left-radius: 3px; transform: translateY(-1px);">
        <i class="la la-{{$column['value']->provider}}" style="transform: translateY(2px);"></i>
    </a><img src="{{$column['value']->image}}" alt="{{$column['value']->title}}" style="height: 25px; border-top-right-radius: 3px; border-bottom-right-radius: 3px;" />
    @else
    {{ $column['default'] ?? '-' }}
    @endif
</span>
