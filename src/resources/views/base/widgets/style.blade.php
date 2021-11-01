@push($widget['stack'] ?? 'after_styles')

    <link href="{{ asset($widget['content'] ?? $widget['path']) }}" rel="stylesheet" type="text/css" >

@endpush
