<!-- select_and_order -->
@php
    $values = (array) $field['value'];
@endphp
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <div>
        <ul id="{{ $field['name'] }}_all" class="{{ $field['name'] }}_connectedSortable select_and_order_all">
            @if(old($field["name"]))
                @foreach ($field['options'] as $key => $value)
                    @if(!is_array(old($field["name"])) || !in_array($key, old($field["name"])))
                        <li value="{{ $key}}">{{ $value }}</li>
                    @endif
                @endforeach
            @elseif (isset($field['options']))
                @foreach ($field['options'] as $key => $value)
                    @if(is_array($values) && !in_array($key, $values))
                        <li value="{{ $key}}">{{ $value }}</li>
                    @endif
                @endforeach
            @endif
        </ul>
        <ul id="{{ $field['name'] }}_selected" class="{{ $field['name'] }}_connectedSortable select_and_order_selected">
            @if(old($field["name"]))
                @if(is_array(old($field["name"])))
                    @foreach (old($field["name"]) as $key)
                        @if(array_key_exists($key,$field['options']))
                            <li value="{{$key}}">{{ $field['options'][$key] }}</li>
                        @endif
                    @endforeach
                @endif
            @elseif (is_array($values))
                @foreach ($values as $key)
                    @if(array_key_exists($key,$field['options']))
                    <li value="{{$key}}">{{ $field['options'][$key] }}</li>
                    @endif
                @endforeach
            @endif
        </ul>

        {{-- The results will be stored here --}}
        <div id="{{ $field['name'] }}_results"></div>
    </div>



    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <style>
        .select_and_order_all, .select_and_order_selected {
            border: 1px solid #d2d6de;
            width: 202px;
            min-height: 40px;
            list-style-type: none;
            margin-right: 20px;
            padding: 5px 0 0 0;
            float: left;
            max-height: 190px;
            overflow: scroll;
            overflow-x: hidden;
        }
        .select_and_order_all li, .select_and_order_selected li{
            border: 1px solid #eee;
            margin: 0 5px 5px 5px;
            padding: 5px;
            font-size: 1.2em;
            width: 175px;
            overflow:hidden;
            cursor:grab
        }
    </style>
    @endpush

    @push('crud_fields_scripts')
    <script src="{{ asset('vendor/adminlte/bower_components/jquery-ui/jquery-ui.js') }}"></script>
    @endpush

@endif

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
<script>
    jQuery(document).ready(function($) {
        $( "#{{ $field['name'] }}_all, #{{ $field['name'] }}_selected" ).sortable({
            connectWith: ".{{ $field['name'] }}_connectedSortable",
            update: function() {
                var updatedlist = $(this).attr('id');
                if((updatedlist == "{{ $field['name'] }}_selected")) {
                    $("#{{ $field['name'] }}_results").html("");
                    if($("#{{ $field['name'] }}_selected").find('li').length==0) {
                        var input = document.createElement("input");
                        input.setAttribute('name',"{{ $field['name'] }}");
                        input.setAttribute('value',null);
                        input.setAttribute('type','hidden');
                        $("#{{ $field['name'] }}_results").append(input);
                    } else {
                        $("#{{ $field['name'] }}_selected").find('li').each(function(val,obj) {
                            var input = document.createElement("input");
                            input.setAttribute('name',"{{ $field['name'] }}[]");
                            input.setAttribute('value',obj.getAttribute('value'));
                            input.setAttribute('type','hidden');
                            $("#{{ $field['name'] }}_results").append(input);
                        });
                    }
                }
            }
        }).disableSelection();
    });
</script>
@endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
