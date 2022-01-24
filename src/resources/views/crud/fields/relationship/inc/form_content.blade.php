<input type="hidden" name="_http_referrer" value={{ old('_http_referrer') ?? \URL::previous() ?? url($crud->route) }}>

{{-- See if we're using tabs --}}
@if ($crud->tabsEnabled() && count($crud->getTabs()))
    @include('crud::fields.relationship.inc.show_tabbed_fields')
    <input type="hidden" name="_current_tab" value="{{ Str::slug($crud->getTabs()[0], "") }}" />
@else
  <div class="card">
    <div class="card-body row">
      @include('crud::fields.relationship.inc.show_fields', ['fields' => $crud->fields()])
    </div>
  </div>
@endif


