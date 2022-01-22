{{--
    This field is a switchboard for the "real" field that is a repeatable
    Based on developer preferences and the relation type we "guess" the best solution
    we can provide for the user and setup some defaults for them.
    One of the things that we take care, is adding the "pivot selector field", that is the link with
    the current crud and pivot entries, in this scenario is used with other pivot fields in a repeatable container.
--}}

@php
    $field['type'] = 'repeatable';
    //each row represent a related entry in a database table. We should not "auto-add" one relationship if it's not the user intention.
    $field['init_rows'] = 0;
    $field['subfields'] = $field['subfields'] ?? [];
    $field['reorder'] = $field['reorder'] ?? false;
    
    $pivotSelectorField = $field['pivotSelect'] ?? [];
    $inline_create = !isset($inlineCreate) && isset($pivotSelectorField['inline_create']) ? $pivotSelectorField['inline_create'] : false;
    $pivotSelectorField['name'] = $field['name'];
    $pivotSelectorField['type'] = 'relationship';
    $pivotSelectorField['is_pivot_select'] = true;
    $pivotSelectorField['multiple'] = false;
    $pivotSelectorField['entity'] = $field['name'];    
    $pivotSelectorField['ajax'] = $inline_create !== false ? true : ($pivotSelectorField['ajax'] ?? false);
    $pivotSelectorField['data_source'] = $pivotSelectorField['data_source'] ?? ($pivotSelectorField['ajax'] ? url($crud->route.'/fetch/'.$field['entity']) : 'false');
    $pivotSelectorField['minimum_input_length'] = $pivotSelectorField['minimum_input_length'] ?? 2;
    $pivotSelectorField['delay'] = $pivotSelectorField['delay'] ?? 500;
    $pivotSelectorField['placeholder'] = $pivotSelectorField['placeholder'] ?? trans('backpack::crud.select_entry');
    $pivotSelectorField['baseModel'] = $pivotSelectorField['baseModel'] ?? get_class($crud->model);
    
    switch ($field['relation_type']) {
        case 'MorphToMany':
        case 'BelongsToMany':
            $field['subfields'] = Arr::prepend($field['subfields'], $pivotSelectorField);
            break;
        case 'MorphMany':
        case 'HasMany':
            if(isset($entry)) {
                $field['subfields'] = Arr::prepend($field['subfields'], [
                    'name' => $entry->{$field['name']}()->getLocalKeyName(),
                    'type' => 'hidden',
                ]);
            }
        break;
    }
@endphp

@include('crud::fields.repeatable')
