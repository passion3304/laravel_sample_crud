{{-- REPEATABLE FIELD TYPE --}}
<div @include('crud::inc.field_wrapper_attributes') >
  <label>{!! $field['label'] !!}</label>
  <input
      type="hidden"
      name="{{ $field['name'] }}"
      data-init-function="bpFieldInitRepeatableElement"
      value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
      @include('crud::inc.field_attributes')
  >

  <div class="container-repeatable-elements">
    <div class="col-md-12 well repeatable-element row m-1 p-2">
      @if (isset($field['fields']) && is_array($field['fields']) && count($field['fields']))
        <button type="button" class="close delete-element"><span aria-hidden="true">×</span></button>
        @foreach($field['fields'] as $subfield)
          @php
              $fieldViewNamespace = $subfield['view_namespace'] ?? 'crud::fields';
              $fieldViewPath = $fieldViewNamespace.'.'.$subfield['type'];
              $subfield['showAsterisk'] = false;
          @endphp

          @include($fieldViewPath, ['field' => $subfield])
        @endforeach

      @endif
    </div>

  </div>
  <button type="button" class="btn btn-primary float-right repeatable-elements">+</button>

  {{-- HINT --}}
  @if (isset($field['hint']))
      <p class="help-block">{!! $field['hint'] !!}</p>
  @endif
</div>

@if ($crud->fieldTypeNotLoaded($field))
  @php
      $crud->markFieldTypeAsLoaded($field);
  @endphp
  {{-- FIELD EXTRA CSS  --}}
  {{-- push things in the after_styles section --}}

  @push('crud_fields_styles')
      <!-- no styles -->
      <style type="text/css">
        .repeatable-element {
          border: 1px solid rgba(0,40,100,.12);
          border-radius: 5px;
          background-color: #fbfbfb;
        }
        .container-repeatable-elements .delete-element {
          z-index: 99;
          position: absolute!important;
          margin-left: -24px;
          margin-top: 0px;
          height: 30px;
          width: 30px;
          border-radius: 15px;
          text-align: center;
          background-color: #c0d0ed!important;
        }
      </style>
  @endpush

  {{-- FIELD EXTRA JS --}}
  {{-- push things in the after_scripts section --}}

  @push('crud_fields_scripts')
      <script>
        /**
         * Takes all inputs and makes them an object.
         */
        function repeatableInputToObj(container) {
            var arr = [];
            var obj = {};

            container.find('.well').each(function () {
                $(this).find('input, select, textarea').each(function () {
                    if ($(this).data('secondary-name')) {
                        obj[$(this).data('secondary-name')] = $(this).val();
                    }
                });
                arr.push(obj);
                obj = {};
            });

            return arr;
        }

        /**
         * The method that initializes the javascript on this field type.
         */
        function bpFieldInitRepeatableElement(element) {
            // element will be a jQuery wrapped DOM node
            var container = element.siblings('.container-repeatable-elements');

            // make sure the inputs no longer have a "name" attribute, 
            // so that the form will not send the inputs as request variables;
            // use a "data-secondary-name" attribute to store the same information;
            container.find('input, select, textarea')
                    .each(function(){
                        if ($(this).data('name')) {
                            var name_attr = $(this).data('name');
                            $(this).removeAttr("data-name");
                        } else if ($(this).attr('name')) {
                            var name_attr = $(this).attr('name');
                            $(this).removeAttr("name");
                        }
                        $(this).attr('data-secondary-name', name_attr)
                               .val('');
                    });

            // make a copy of the group of inputs in their default state
            // this way we have a clean element we can clone when the user 
            // wants to add a new group of inputs
            var field_group = container.find('.repeatable-element:first').clone();
            container.find('.repeatable-element').remove();

            element.parent().find('.repeatable-elements').click(function(){
                newRepeatableElement(container,field_group);
            });

            if (element.val()) {
                var repeatable_fields_values = JSON.parse(element.val());

                for (var i = 0; i < repeatable_fields_values.length; ++i) {
                    newRepeatableElement(container,field_group,repeatable_fields_values[i]);
                }
            } else {
                element.parent().find('.repeatable-elements').trigger('click');
            }

            if (element.closest('.modal-content').length) {
                element.closest('.modal-content').find('.save-block').click(function(){
                    element.val(JSON.stringify(repeatableInputToObj(container)));
                })
            } else if (element.closest('form').length) {
                element.closest('form').submit(function(){
                    element.val(JSON.stringify(repeatableInputToObj(container)));
                    return true;
                })
            }
        }

        /**
         * Adds a new field group to the repeatable input.
         */
        function newRepeatableElement(container, field_group, values) {
            var new_field_group = field_group.clone();

            new_field_group.find('.delete-element').click(function(){
                $(this).parent().remove();
            });

            if (values != null) {
                new_field_group.find('input, select, textarea').each(function () {
                    if ($(this).data('secondary-name')) {
                        $(this).val(values[$(this).data('secondary-name')]);
                    }
                });
            }
            container.append(new_field_group);
            initializeFieldsWithJavascript(container);
        }
    </script>
  @endpush
@endif