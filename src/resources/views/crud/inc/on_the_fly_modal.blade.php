@php
    if (session()->has('current_crud_loaded_fields')) {
        $loadedFields = session('current_crud_loaded_fields');
        session()->forget('current_crud_loaded_fields');
    }

    $loadedFields = $loadedFields ?? [];

    //mark parent crud fields as loaded in DOM.
    foreach($loadedFields as $loadedField) {
        $crud->markFieldTypeAsLoaded($loadedField);
    }

@endphp
<div class="modal fade" id="{{$entity}}-on-the-fly-create-dialog" tabindex="-1" role="dialog" aria-labelledby="{{$entity}}-on-the-fly-create-dialog-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="{{$entity}}-on-the-fly-create-dialog-label">New {{$entity}}</h5>
        </div>
        <div class="modal-body">
            <form method="post"
            id="{{$entity}}-on-the-fly-create-form"
            action="#"
          @if ($crud->hasUploadFields('create'))
          enctype="multipart/form-data"
          @endif
            >
        {!! csrf_field() !!}

        <!-- load the view from the application if it exists, otherwise load the one in the package -->
        @if(view()->exists('vendor.backpack.crud.on_the_fly_form_content'))
            @include('vendor.backpack.crud.on_the_fly_form_content', [ 'fields' => $fields, 'action' => $action])
        @else
            @include('crud::on_the_fly_form_content', [ 'fields' => $fields, 'action' => $action])
        @endif


    </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="cancelButton" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveButton">Save</button>
        </div>
      </div>
    </div>
  </div>
  @stack('modal_loaded_fields_styles')
  @stack('modal_loaded_fields_scripts')


        {{-- YOUR JS HERE --}}


