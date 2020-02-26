
<{{ $column['wrapper']['element'] }}
@foreach(Illuminate\Support\Arr::where($column['wrapper'],function($value, $key) { return $key != 'element'; }) as $element => $value)
    {{$element}}="{{$value}}"
@endforeach
>

