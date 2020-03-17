@php
    $value = data_get($entry, $column['name']);
    $column['prefix'] = $column['prefix'] ?? '';

    $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    $column['wrapper']['target'] = $column['wrapper']['target'] ?? '_blank';

    $column['escaped'] = $column['escaped'] ?? true;
@endphp

<span>
    @if ($value && count($value))
        @foreach ($value as $file_path)
        @php
            $column['wrapper']['href'] = $column['wrapper']['href'] ?? isset($column['disk'])?asset(\Storage::disk($column['disk'])->url($file_path)):asset($prefix.$file_path);
            $text = $prefix.$file_path;
        @endphp
            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
            @if($column['escaped'])
                - {{ $text }}
            @else
                - {!! $text !!}
            @endif
        @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
        @endforeach
    @else
        -
    @endif
</span>
