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

    // this needs to be checked here because they depend on a blade variable `$inlineCreate` that prevents the modal over modal scenario
    $inline_create = !isset($inlineCreate) && isset($pivotSelectorField['inline_create']) ? $pivotSelectorField['inline_create'] : false;
    $pivotSelectorField['ajax'] = $inline_create !== false ? true : ($pivotSelectorField['ajax'] ?? false);
    $pivotSelectorField['data_source'] = $pivotSelectorField['data_source'] ?? ($pivotSelectorField['ajax'] ? url($crud->route.'/fetch/'.$field['entity']) : 'false');

    $field['subfields'] = array_map(function($subfield) use ($pivotSelectorField) {
        if(isset($subfield['is_pivot_select'])) {
            $subfield = array_merge($subfield, $pivotSelectorField);
        }
        return $subfield;
    },$field['subfields']);
@endphp

@include('crud::fields.repeatable')
