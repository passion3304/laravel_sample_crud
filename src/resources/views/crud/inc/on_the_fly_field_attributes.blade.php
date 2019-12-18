data-on-the-fly-create-route="{{$createRoute ?? false}}"
data-on-the-fly-update-route="{{$updateRoute ?? false}}"
data-on-the-fly-refresh-route="{{$refreshRoute ?? false}}"

data-field-related-name="{{$onTheFlyEntity}}"
data-on-the-fly-create-button="{{ $onTheFlyEntity }}-on-the-fly-create-{{$field['name']}}"
data-on-the-fly-allow-create="{{var_export($activeOnTheFlyCreate)}}"
