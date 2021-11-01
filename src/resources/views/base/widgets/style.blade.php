@push($widget['stack'] ?? 'after_styles')

    <link href="{{ asset($widget['content'] ?? $widget['path']) }}" rel="stylesheet" type="text/css" 
        @isset($widget['integrity'])
            integrity="{{ $widget['integrity'] }}"
        @endisset
        @isset($widget['crossorigin'])
            crossorigin="{{ $widget['crossorigin'] }}"
        @endisset
        >

@endpush
