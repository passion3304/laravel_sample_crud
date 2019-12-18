<input type="hidden" name="http_referrer" value={{ old('http_referrer') ?? \URL::previous() ?? url($crud->route) }}>

{{-- See if we're using tabs --}}
@if ($crud->tabsEnabled() && count($crud->getTabs()))
    @include('crud::inc.show_tabbed_fields_on_the_fly', ['onTheFly' => 'true'])
    <input type="hidden" name="current_tab" value="{{ str_slug($crud->getTabs()[0], "") }}" />
@else
  <div class="card">
    <div class="card-body row">
      @include('crud::inc.show_fields', ['fields' => $crud->fields(), 'onTheFly' => 'true'])
    </div>
  </div>
@endif
@push('modal_loaded_fields_scripts')
@stack('crud_scripts_pre')
@endpush

@push('modal_loaded_fields_styles')
@stack('crud_styles_pre')
@endpush
