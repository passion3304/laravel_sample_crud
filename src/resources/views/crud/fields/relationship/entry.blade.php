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
    $field['max_rows'] = 1;
    $field['reorder'] = $field['reorder'] ?? false;
@endphp

@include('crud::fields.repeatable')