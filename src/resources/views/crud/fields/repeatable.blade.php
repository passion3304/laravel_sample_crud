{{-- REPEATABLE FIELD TYPE --}}

@php
  $field['value'] = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : [] ));
  // make sure the value is always an array, even if stored as JSON in database
  $field['value'] = is_string($field['value']) ? json_decode($field['value'], true) : $field['value'];

  $field['init_rows'] = $field['init_rows'] ?? $field['min_rows'] ?? 1;
  $field['max_rows'] = $field['max_rows'] ?? 0;
  $field['min_rows'] =  $field['min_rows'] ?? 0;
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
<input
    type="hidden"
    name="{{ $field['name'] }}"
    data-init-function="bpFieldInitRepeatableElement"
    @include('crud::fields.inc.attributes')
>


<div class="container-repeatable-elements">
    <div
        data-repeatable-holder="{{ $field['name'] }}"
        data-init-rows="{{ $field['init_rows'] }}"
        data-max-rows="{{ $field['max_rows'] }}"
        data-min-rows="{{ $field['min_rows'] }}"
    >
    @if(!empty($field['value']))
        @foreach ($field['value'] as $key => $row)

            <div class="col-md-12 well repeatable-element row m-1 p-2" data-repeatable-identifier="{{ $field['name'] }}">
                @if (isset($field['fields']) && is_array($field['fields']) && count($field['fields']))
                <div class="controls">
                    <button type="button" class="close delete-element"><span aria-hidden="true">×</span></button>
                    <button type="button" class="close move-element-up">
                        <svg viewBox="0 0 64 80"><path d="M46.8,36.7c-4.3-4.3-8.7-8.7-13-13c-1-1-2.6-1-3.5,0c-4.3,4.3-8.7,8.7-13,13c-2.3,2.3,1.3,5.8,3.5,3.5c4.3-4.3,8.7-8.7,13-13c-1.2,0-2.4,0-3.5,0c4.3,4.3,8.7,8.7,13,13C45.5,42.5,49,39,46.8,36.7L46.8,36.7z"/></svg>
                    </button>
                    <button type="button" class="close move-element-down">
                        <svg viewBox="0 0 64 80"><path d="M17.2,30.3c4.3,4.3,8.7,8.7,13,13c1,1,2.6,1,3.5,0c4.3-4.3,8.7-8.7,13-13c2.3-2.3-1.3-5.8-3.5-3.5c-4.3,4.3-8.7,8.7-13,13c1.2,0,2.4,0,3.5,0c-4.3-4.3-8.7-8.7-13-13C18.5,24.5,15,28,17.2,30.3L17.2,30.3z"/></svg>
                    </button>
                </div>
                @foreach($field['fields'] as $subfield)
                    @php
                        $subfield = $crud->makeSureFieldHasNecessaryAttributes($subfield);
                        $fieldViewNamespace = $subfield['view_namespace'] ?? 'crud::fields';
                        $fieldViewPath = $fieldViewNamespace.'.'.$subfield['type'];
                        if(!is_array($subfield['name'])) {
                            if(isset($row[$subfield['name']])) {
                                $subfield['value'] = $row[$subfield['name']];
                            }
                        }else{
                            
                            $subfield['value'] = $row;
                        }                       
                    @endphp

                    @include($fieldViewPath, ['field' => $subfield])
                @endforeach

                @endif
            </div>
        @endforeach
    @endif
    </div>
</div>

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block text-muted text-sm">{!! $field['hint'] !!}</p>
@endif
<button type="button" class="btn btn-outline-primary btn-sm ml-1 add-repeatable-element-button">+ {{ $field['new_item_label'] ?? trans('backpack::crud.new_item') }}</button>

@include('crud::fields.inc.wrapper_end')
@push('before_scripts')
<div class="col-md-12 well repeatable-element row m-1 p-2" data-repeatable-identifier="{{ $field['name'] }}">
  @if (isset($field['fields']) && is_array($field['fields']) && count($field['fields']))
    <div class="controls">
        <button type="button" class="close delete-element"><span aria-hidden="true">×</span></button>
        <button type="button" class="close move-element-up">
            <svg viewBox="0 0 64 80"><path d="M46.8,36.7c-4.3-4.3-8.7-8.7-13-13c-1-1-2.6-1-3.5,0c-4.3,4.3-8.7,8.7-13,13c-2.3,2.3,1.3,5.8,3.5,3.5c4.3-4.3,8.7-8.7,13-13c-1.2,0-2.4,0-3.5,0c4.3,4.3,8.7,8.7,13,13C45.5,42.5,49,39,46.8,36.7L46.8,36.7z"/></svg>
        </button>
        <button type="button" class="close move-element-down">
            <svg viewBox="0 0 64 80"><path d="M17.2,30.3c4.3,4.3,8.7,8.7,13,13c1,1,2.6,1,3.5,0c4.3-4.3,8.7-8.7,13-13c2.3-2.3-1.3-5.8-3.5-3.5c-4.3,4.3-8.7,8.7-13,13c1.2,0,2.4,0,3.5,0c-4.3-4.3-8.7-8.7-13-13C18.5,24.5,15,28,17.2,30.3L17.2,30.3z"/></svg>
        </button>
    </div>
    @foreach($field['fields'] as $subfield)
      @php
          $subfield = $crud->makeSureFieldHasNecessaryAttributes($subfield);
          $fieldViewNamespace = $subfield['view_namespace'] ?? 'crud::fields';
          $fieldViewPath = $fieldViewNamespace.'.'.$subfield['type'];
      @endphp
      @include($fieldViewPath, ['field' => $subfield])
    @endforeach
  @endif
</div>
@endpush
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
          background-color: #f0f3f94f;
        }
        .container-repeatable-elements .controls {
          display: flex;
          flex-direction: column;
          align-content: center;
          position: absolute !important;
          left: -16px;
          z-index: 2;
        }

        .container-repeatable-elements .controls button {
          height: 30px;
          width: 30px;
          border-radius: 50%;
          background-color: #e8ebf0 !important;
          margin-bottom: 2px;
          overflow: hidden;
        }
        .container-repeatable-elements .controls button.move-element-up,
        .container-repeatable-elements .controls button.move-element-down {
            height: 24px;
            width: 24px;
            margin: 2px auto;
        }
        .container-repeatable-elements .repeatable-element:first-of-type .move-element-up,
        .container-repeatable-elements .repeatable-element:last-of-type .move-element-down {
            display: none;
        }
      </style>
  @endpush

  {{-- FIELD EXTRA JS --}}
  {{-- push things in the after_scripts section --}}

  @push('crud_fields_scripts')
      <script>
        /**
         * Takes all inputs in a repeatable element and makes them an object.
         */
        function repeatableElementToObj(element) {
            var obj = {};

            element.find('input, select, textarea').each(function () {
                if ($(this).data('repeatable-input-name')) {
                    obj[$(this).data('repeatable-input-name')] = $(this).val();
                }
            });

            return obj;
        }

        /**
         * The method that initializes the javascript on this field type.
         */
        function bpFieldInitRepeatableElement(element) {

            var field_name = element.attr('name');

            var container_holder = $('[data-repeatable-holder='+field_name+']');

            var init_rows = Number(container_holder.attr('data-init-rows'));
            var min_rows = Number(container_holder.attr('data-min-rows'));
            var max_rows = Number(container_holder.attr('data-max-rows')) || Infinity;

            // make a copy of the group of inputs in their default state
            // this way we have a clean element we can clone when the user
            // wants to add a new group of inputs
            var container = $('[data-repeatable-identifier='+field_name+']').last();
            // make sure the inputs get the data-repeatable-input-name
            // so we can know that they are inside repeatable
            container.find('input, select, textarea')
                    .each(function(){
                        var name_attr = getCleanNameArgFromInput($(this));
                        $(this).attr('data-repeatable-input-name', name_attr)
                    });

            var field_group_clone = container.clone();
            container.remove();
            
            element.parent().find('.add-repeatable-element-button').click(function(){
                newRepeatableElement(container_holder, field_group_clone);
            });

            var container_rows = container_holder.children().length;
            var add_entry_button = element.parent().find('.add-repeatable-element-button');
            if(container_rows === 0) {
                for(let i = 0; i < Math.min(init_rows, max_rows || init_rows); i++) {
                    container_rows++;
                    add_entry_button.trigger('click');
                }
            }

            setupElementRowsNumbers(container_holder);

            setupElementCustomSelectors(container_holder);

            setupRepeatableDeleteRowButtons(container_holder);

            setupRepeatableReorderButtons(container_holder);

            updateRepeatableRowCount(container_holder);

            updateRepeatableContainerNamesIndexes(container_holder)

            initializeFieldsWithJavascript(container_holder);
        }

        /**
         * Adds a new field group to the repeatable input.
         */
        function newRepeatableElement(container_holder, field_group, repeatable_element, position) {

            var new_field_group = field_group.clone();            

            // we push the fields to the correct container in page.
            var $children = container_holder.children();

            if (!Number.isInteger(position) || $children.length - 1 < position) {
                container_holder.append(new_field_group);
            } else {
                $children.eq(position).before(repeatable_element);
            }

            // after appending to the container we reassure row numbers
            setupElementRowsNumbers(container_holder);

            // we also setup the custom selectors in the elements so we can use dependant functionality
            setupElementCustomSelectors(container_holder);

            setupRepeatableDeleteRowButtons(container_holder);

            setupRepeatableReorderButtons(container_holder);

            // updates the row count in repeatable and handle the buttons state
            updateRepeatableRowCount(container_holder);

            // re-index the array names for the fields
            updateRepeatableContainerNamesIndexes(container_holder);

            initializeFieldsWithJavascript(container_holder);

            if (Number.isInteger(position)) {
                // Trigger change for elements that have moved
                new_field_group.find('input, select, textarea').each(function(i, el) {
                    $(el).trigger('change');
                });
            }
        }

        function setupRepeatableDeleteRowButtons(container) {
            container.children().each(function(i, repeatable_group) {
                setupRepeatableDeleteButtonEvent(repeatable_group);
            });
        }

        function setupRepeatableDeleteButtonEvent(repeatable_group) {
            let row = $(repeatable_group);
            let delete_button = row.find('.delete-element');
            
            // remove previous events on this button
            delete_button.off('click');

            delete_button.click(function(){

                let $repeatableElement = $(this).closest('.repeatable-element');
                let container = $('[data-repeatable-holder='+$($repeatableElement).attr('data-repeatable-identifier')+']')

                row.find('input, select, textarea').each(function(i, el) {
                    // we trigger this event so fields can intercept when they are beeing deleted from the page
                    // implemented because of ckeditor instances that stayed around when deleted from page
                    // introducing unwanted js errors and high memory usage.
                    $(el).trigger('backpack_field.deleted');
                });

                $repeatableElement.remove();

                // updates the row count and handle button state
                updateRepeatableRowCount(container);

                //we reassure row numbers on delete
                setupElementRowsNumbers(container);

                updateRepeatableContainerNamesIndexes(container);
            });
        }

        function setupRepeatableReorderButtons(container) {
            container.children().each(function(i, repeatable_group) {
                setupRepeatableReorderButtonEvent($(repeatable_group));
            });
        }

        function setupRepeatableReorderButtonEvent(repeatable_group) {
            let row = $(repeatable_group);
            let reorder_buttons = row.find('.move-element-up, .move-element-down');
            
            // remove previous events on this button
            reorder_buttons.off('click');

            reorder_buttons.click(function(e){
                
                let $repeatableElement = $(e.target).closest('.repeatable-element');
                let container = $('[data-repeatable-holder='+$($repeatableElement).attr('data-repeatable-identifier')+']')

                // get existing values
                //let values = repeatableElementToObj($repeatableElement);
                let index = $repeatableElement.index();
    
                index += $(this).is('.move-element-up') ? -1 : 1;

                if (index < 0) return;

                // trigger delete for existing element
                row.find('input, select, textarea').each(function(i, el) {
                    // we trigger this event so fields can intercept when they are beeing deleted from the page
                    // implemented because of ckeditor instances that stayed around when deleted from page
                    // introducing unwanted js errors and high memory usage.
                    $(el).trigger('backpack_field.deleted');
                });

                let $toCreate = $repeatableElement.clone();

                // remove element
                $repeatableElement.remove();

                // create new element with existing values in desired position
                newRepeatableElement(container, repeatable_group, $toCreate, index);
            });
        }

        // this function is responsible for managing rows numbers upon creation/deletion of elements
        function setupElementRowsNumbers(container) {
            var number_of_rows = 0;
            container.children().each(function(i, el) {
                var rowNumber = i+1;
                $(el).attr('data-row-number', rowNumber);
                //also attach the row number to all the input elements inside
                $(el).find('input, select, textarea').each(function(i, input) {
                    // only add the row number to inputs that have name, so they are going to be submited in form
                    if($(input).attr('name')) {
                        $(input).attr('data-row-number', rowNumber);
                    }
                });
                number_of_rows++;
            });

            container.attr('number-of-rows', number_of_rows);
        }

        // this function is responsible for adding custom selectors to repeatable inputs that are selects and could be used with
        // dependant fields functionality
        function setupElementCustomSelectors(container) {
            container.children().each(function(i, el) {
                // attach a custom selector to this elements
                $(el).find('select').each(function(i, select) {
                    let selector = '[data-repeatable-input-name="%DEPENDENCY%"][data-row-number="%ROW%"],[data-repeatable-input-name="%DEPENDENCY%[]"][data-row-number="%ROW%"]';
                    select.setAttribute('data-custom-selector', selector);
                });
            });
        }

        function updateRepeatableContainerNamesIndexes(container) {
            container.children().each(function(i, repeatable) {
                var index = $(repeatable).attr('data-row-number')-1;
                // updates the indexes in the array of repeatable inputs
                $(repeatable).find('input, select, textarea').each(function(i, el) {
                    if(typeof $(el).attr('data-row-number') !== 'undefined') {
                        let field_name = $(el).attr('data-repeatable-input-name') ?? $(el).attr('name') ?? $(el).parent().find('input[data-repeatable-input-name]').first().attr('data-repeatable-input-name');
                        let unprefixed_field_name = field_name.endsWith("[]") ? field_name.substring(0, field_name.length - 2) : field_name;
                        if(typeof $(el).attr('data-repeatable-input-name') === 'undefined') {
                            $(el).attr('data-repeatable-input-name', field_name);
                        }
        
                        let prefix = field_name.endsWith("[]") ? '[]' : '';
                        $(el).attr('name', container.attr('data-repeatable-holder')+'['+index+']['+unprefixed_field_name+']'+prefix);
                    }
                });
            });
        }

        // return the clean name from the input
        function getCleanNameArgFromInput(element) {
            if (element.data('repeatable-input-name')) {
                return element.data('repeatable-input-name');
            }
            if (element.data('name')) {
                return element.data('name');       
            } else if (element.attr('name')) {
               return element.attr('name');
            }
        }

        // update the container current number of rows and work out the buttons state
        function updateRepeatableRowCount(container) {
            let max_rows = Number(container.attr('data-max-rows')) || Infinity;
            let min_rows = Number(container.attr('data-min-rows')) || 0;

            let current_rows =  container.children().length;

            // show or hide delete button
            container.find('.delete-element').toggleClass('d-none', current_rows <= min_rows);

            // show or hide move buttons
            container.find('.move-element-up, .move-element-down').toggleClass('d-none', current_rows <= 1);

            // show or hide new item button
            container.parent().parent().find('.add-repeatable-element-button').toggleClass('d-none', current_rows >= max_rows);

        }
    </script>
  @endpush
@endif
