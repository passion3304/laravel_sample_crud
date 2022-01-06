{{-- 
    This field is a switchboard for the "real" relation fields AKA selects and repeatable.
    Based on developer preferences and the relation type we "guess" the best solution
    we can provide for the user, and use those field types (select/repeatable) accordingly.
    As relationships are the only thing allowed to "InlineCreate", that functionality is also handled here.
    We have a dedicated file for the inline create functionality that is `fetch_or_create`, that is basically
    a select2 with ajax enabled that allow to create a new entity without leaving the crud
--}}

@php
    if(isset($field['inline_create']) && !is_array($field['inline_create'])) {
        $field['inline_create'] = [true];
    }
    $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
    $field['ajax'] = $field['ajax'] ?? isset($field['data_source']);
    $field['placeholder'] = $field['placeholder'] ?? ($field['multiple'] ? trans('backpack::crud.select_entries') : trans('backpack::crud.select_entry'));
    $field['attribute'] = $field['attribute'] ?? (new $field['model'])->identifiableAttribute();
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.
    // if field is not ajax but user wants to use InlineCreate
    // we make minimum_input_length = 0 so when user open we show the entries like a regular select
    $field['minimum_input_length'] = ($field['ajax'] !== true) ? 0 : ($field['minimum_input_length'] ?? 2);

    switch($field['relation_type']) {
        case 'HasOne':
        case 'MorphOne':
            abort("The relationship field does not support {$field['relation_type']} at the moment. Please add a text/number/textarea/etc field, but use dot notation for its name. This will allow you to have a field that edits information directly on the related entry (eg. phone.number). See https://backpackforlaravel.com/docs/crud-fields#hasone-1-1-relationship for more information.");
            // TODO: if relationship has `isOneOfMany` on it, load a readonly select
            // TODO: if "fields" is not defined, tell the dev to define it (+ link to docs)
            // TODO: if "fields" is defined, load a repeatable field with one entry (and 1 entry max)
            // TODO: remove the ugly abort from above
            break;
        case 'BelongsTo':
        case 'BelongsToMany':
        case 'MorphToMany':
            // if there are pivot fields we show the repeatable field
            if(isset($field['pivotFields'])) {
                $field['type'] = 'repeatable_relation';
                break;
            }

            if(!isset($field['inline_create'])) {
                $field['type'] = $field['ajax'] ? 'fetch' : 'relationship_select';
                break;
            }

            // the field is beeing inserted in an inline create modal case $inlineCreate is set.
            if(! isset($inlineCreate)) {
                $field['type'] = 'fetch_or_create';
                break;
            }

    		$field['type'] = $field['ajax'] ? 'fetch' : 'relationship_select';
            break;
        case 'MorphMany':
        case 'HasMany':
            // when set, field value will default to what developer defines
            $field['fallback_id'] = $field['fallback_id'] ?? false;
            // when true, backpack ensures that the connecting entry is deleted when un-selected from relation
            $field['force_delete'] = $field['force_delete'] ?? false;

            // if there are pivot fields we show the repeatable field
            if(isset($field['pivotFields'])) {
                $field['type'] = 'repeatable_relation';
            } else {
                // we show a regular/ajax select
                $field['type'] = $field['ajax'] ? 'fetch' : 'relationship_select';
            }
            break;
        case 'HasOneThrough':
        case 'HasManyThrough':
            abort("The relationship field does not support {$field['relation_type']} at the moment. This is a 'readonly' relationship type. When we do add support for it, it the field only SHOW the related entries, NOT allow you to select/edit them.");
            // TODO: load a readonly select for that chained relationship, and remove the abort above
            break;
        default:
            abort("Unknown relationship type used with the 'relationship' field. Please let the Backpack team know of this new Laravel relationship, so they add support for it.");
            break;
    }
@endphp

@include('crud::fields.relationship.'.$field['type'])