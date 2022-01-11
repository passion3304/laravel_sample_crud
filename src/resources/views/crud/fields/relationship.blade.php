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
            // if the related attribute was given, through dot notation
            // then we show a text field for it
            if (str_contains($field['entity'], '.')) {
                $field['type'] = 'text';
                break;
            }

            // TODO: if relationship has `isOneOfMany` on it, load a readonly select; this covers:
            // - has One Of Many - hasOne(Order::class)->latestOfMany()
            // - morph One Of Many - morphOne(Image::class)->latestOfMany()
            $relationship = CRUD::getModel()->{$field['entity']}();
            if ($relationship->isOneOfMany()) {
                abort(500, "<strong>The relationship field type does not cover 'One of Many' relationships.</strong><br> Those relationship are only meant to be 'read', not 'created' or 'updated'. Please change your <code>{$field['name']}</code> field to use the 1-n relationship towards <code>{$field['model']}</code>, the one that does NOT have latestOfMany() or oldestOfMany(). See <a target='_blank' href='https://backpackforlaravel.com/docs/crud-fields#has-one-of-many-1-1-relationship-out-of-1-n-relationship'>the docs</a> for more information.");
            }


            // the dev is trying to create a field for the ENTIRE hasOne/morphOne relationship
            abort(500, "<strong>The relationship field does not support <code>{$field['relation_type']}</code> that way.</strong><br> Please change your <code>{$field['name']}</code> field so that its <i>name</i> also includes the editable attribute on the related model, using dot notation (eg. <code>address.postal_code</code>). If you need to edit more attributes, add a new field for each one (eg. <code>address.postal_code</code>). See <a target='_blank' href='https://backpackforlaravel.com/docs/crud-fields#hasone-1-1-relationship'>the docs</a> for more information.");
            // TODO: if "fields" is not defined, tell the dev to define it (+ link to docs)
            // TODO: if "fields" is defined, load a repeatable field with one entry (and 1 entry max)
            break;
        case 'BelongsTo':
        case 'BelongsToMany':
        case 'MorphToMany':
            // if there are pivot fields we show the repeatable field
            if(isset($field['pivotFields'])) {
                $field['type'] = 'relationship.repeatable_relation';
                break;
            }

            if(!isset($field['inline_create'])) {
                $field['type'] = $field['ajax'] ? 'relationship.fetch' : 'relationship.relationship_select';
                break;
            }

            // the field is beeing inserted in an inline create modal case $inlineCreate is set.
            if(! isset($inlineCreate)) {
                $field['type'] = 'relationship.fetch_or_create';
                break;
            }

    		$field['type'] = $field['ajax'] ? 'relationship.fetch' : 'relationship.relationship_select';
            break;
        case 'HasMany':
        case 'MorphMany':
            // when set, field value will default to what developer defines
            $field['fallback_id'] = $field['fallback_id'] ?? false;
            // when true, backpack ensures that the connecting entry is deleted when un-selected from relation
            $field['force_delete'] = $field['force_delete'] ?? false;

            // if there are pivot fields we show the repeatable field
            if(isset($field['pivotFields'])) {
                $field['type'] = 'relationship.repeatable_relation';
            } else {
                // we show a regular/ajax select
                $field['type'] = $field['ajax'] ? 'relationship.fetch' : 'relationship.relationship_select';
            }
            break;
        case 'HasOneThrough':
        case 'HasManyThrough':
            abort(500, "The relationship field does not support {$field['relation_type']} at the moment. This is a 'readonly' relationship type. When we do add support for it, it the field only SHOW the related entries, NOT allow you to select/edit them.");
            // TODO: load a readonly select for that chained relationship, and remove the abort above
            break;
        case 'MorphTo':
        case 'MorphedByMany':
            abort(500, "The relationship field does not support {$field['relation_type']} at the moment, nobody asked for it yet. If you do, please let us know here - https://github.com/Laravel-Backpack/CRUD/issues");
            // TODO: complex interface that allows you to select entries from multiple models
        default:
            abort(500, "Unknown relationship type used with the 'relationship' field. Please let the Backpack team know of this new Laravel relationship, so they add support for it.");
            break;
    }
@endphp

@include('crud::fields.'.$field['type'])
