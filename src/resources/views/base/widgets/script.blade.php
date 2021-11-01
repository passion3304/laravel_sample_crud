@push($widget['stack'] ?? 'after_scripts')

    <script src="{{ asset($widget['content'] ?? $widget['path']) }}"
        {{-- Allow secure use of CDN assets by specifying integrity hash and crossorigin-anonymous  --}}
        @isset($widget['integrity'])
            integrity="{{ $widget['integrity'] }}"
        @endisset
        @isset($widget['crossorigin'])
            crossorigin="{{ $widget['crossorigin'] }}"
        @endisset
    ></script>

@endpush
