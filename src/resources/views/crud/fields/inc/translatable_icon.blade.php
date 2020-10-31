@php
    //if field name is array we check if any of the arrayed fields is translatable
    $translatable = false;
    if($crud->model->translationEnabled()) {
        foreach((array) $field['name'] as $field_name){
            if($crud->model->isTranslatableAttribute($field_name)) {
                $translatable = true;
            }
        }
    }

@endphp
@if ($translatable || (isset($field['store_in']) ? ($translationsEnabled ? $crud->model->isTranslatableAttribute($field['store_in']) : false) : false) && config('backpack.crud.show_translatable_field_icon'))
<i class="la la-flag-checkered pull-{{ config('backpack.crud.translatable_field_icon_position') }}" style="margin-top: 3px;" title="This field is translatable."></i>
@endif
