<span class="browse btn btn-sm btn-light move" id="{{ $onTheFlyEntity }}-on-the-fly-create-{{$name}}" type="button"><span class="fa fa-plus"></span> New</span>

@include('crud::inc.full_screen_loader')

@push('on_the_fly_styles')
@stack('loading_styles')
@endpush

@push('on_the_fly_scripts')
@stack('loading_scripts')
@endpush
