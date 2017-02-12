@if ($crud->model->translationEnabled())
<input type="hidden" name="locale" value={{ $crud->request->input('locale')?$crud->request->input('locale'):App::getLocale() }}>
@endif

{{-- See if we're using tabs --}}
@if ($crud->tabsEnabled())

    @include('crud::inc.show_tabs')

@else

    @include('crud::inc.show_fields', ['fields' => $fields])

@endif

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@section('after_styles')
    <!-- CRUD FORM CONTENT - crud_fields_styles stack -->
    @stack('crud_fields_styles')
@endsection

@section('after_scripts')
    <!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
    @stack('crud_fields_scripts')

    <script>
        jQuery('document').ready(function($){

          //Save button dropdown toggles
          var saveActions = $('#saveActions'),
          crudForm        = saveActions.parents('form'),
          saveActionField = $('[name="save_action"]');

          saveActions.on('click', '.dropdown-menu a', function(){
              var saveAction = $(this).data('value');
              saveActionField.val( saveAction );
              crudForm.submit();
          });

            // Ctrl+S and Cmd+S trigger Save button click
            $(document).keydown(function(e) {
                if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey))
                {
                    e.preventDefault();
                    // alert("Ctrl-s pressed");
                    $("button[type=submit]").trigger('click');
                    return false;
                }
                return true;
            });

          @if( $crud->autoFocusOnFirstField )
            //Focus on first field
            @php
              $focusField = array_first($fields, function($field){
                  return isset($field['auto_focus']) && $field['auto_focus'] == true;
              })
            @endphp

            @if($focusField)
              window.focusField = $('[name="{{ $focusField['name'] }}"]').eq(0),
            @else
              var focusField = $('form').find('input, textarea, select').not('[type="hidden"]').eq(0),
            @endif

            fieldOffset = focusField.offset().top,
            scrollTolerance = $(window).height() / 2;

            focusField.trigger('focus');

            if( fieldOffset > scrollTolerance ){
                $('html, body').animate({scrollTop: (fieldOffset - 30)});
            }
          @endif
        });
    </script>
@endsection
