<!-- array input -->

<?php
    $max = isset($field['max']) && (int) $field['max'] > 0 ? $field['max'] : -1;
    $min = isset($field['min']) && (int) $field['min'] > 0 ? $field['min'] : -1;
    $item_name = strtolower(isset($field['entity_singular']) && !empty($field['entity_singular']) ? $field['entity_singular'] : $field['label']);

    $items = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';

    // make sure not matter the attribute casting
    // the $items variable contains a properly defined JSON
    if (is_array($items)) {
        if (count($items)) {
            $items = json_encode($items);
        } else {
            $items = '[]';
        }
    } elseif (is_string($items) && !is_array(json_decode($items))) {
        $items = '[]';
    }

?>
<div id="backPackTableApp" @include('crud::inc.field_wrapper_attributes') >

    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    <input class="array-json" type="hidden" id="{{ $field['name'] }}" name="{{ $field['name'] }}">

    <div class="array-container form-group">

        <table class="table table-bordered table-striped m-b-0" data-field="#{{ $field['name'] }}" data-items="{{ $items }}" data-max="{{$max}}" data-min="{{$min}}" data-maxErrorTitle="{{trans('backpack::crud.table_cant_add', ['entity' => $item_name])}}" data-maxErrorMessage="{{trans('backpack::crud.table_max_reached', ['max' => $max])}}">

            <thead>
                <tr>
                    @foreach( $field['columns'] as $prop )
                    <th style="font-weight: 600!important;">
                        {{ $prop }}
                    </th>
                    @endforeach
                    <th class="text-center"> {{-- <i class="fa fa-sort"></i> --}} </th>
                    <th class="text-center"> {{-- <i class="fa fa-trash"></i> --}} </th>
                </tr>
            </thead>

            <tbody id="sortableOptions" class="table-striped items">

                <tr class="array-row clonable" style="display: none;">
                    @foreach( $field['columns'] as $prop => $label)
                    <td>
                        <input class="form-control input-sm" type="text" name="item.{{ $prop }}">
                    </td>
                    @endforeach
                    <td>
                        <span class="btn btn-sm btn-default sort-handle"><span class="sr-only">sort item</span><i class="fa fa-sort" role="presentation" aria-hidden="true"></i></span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-default removeItem" type="button"><span class="sr-only">delete item</span><i class="fa fa-trash" role="presentation" aria-hidden="true"></i></button>
                    </td>
                </tr>

            </tbody>

        </table>

        <div class="array-controls btn-group m-t-10">
            <button class="btn btn-sm btn-default" type="button" id="addItem"><i class="fa fa-plus"></i> {{trans('backpack::crud.add')}} {{ $item_name }}</button>
        </div>

    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    {{-- @push('crud_fields_styles')
        {{-- YOUR CSS HERE --}}
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        {{-- YOUR JS HERE --}}
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

        <script>
            jQuery(document).ready(function($) {
                var $tableObj = $('#backPackTableApp');
                var $addItem = $tableObj.find('#addItem');
                var $removeItem = '.removeItem';

                var $max = $tableObj.find('table').attr('data-max');
                var $min = $tableObj.find('table').attr('data-min');

                var $maxErrorTitle = $tableObj.find('table').attr('data-maxErrorTitle');
                var $maxErrorMessage = $tableObj.find('table').attr('data-maxErrorMessage');

                var $field = $($tableObj.find('table').attr('data-field'));

                var $items = $('.items');

                var items = $tableObj.find('table').attr('data-items');

                $('#sortableOptions').sortable({
                    handle: '.sort-handle',
                    axis: 'y',
                    helper: function(e, ui) {
                        ui.children().each(function() {
                            $(this).width($(this).width());
                        });
                        return ui;
                    },
                    update: function( event, ui ) {
                        html2json($tableObj.find('tbody tr:visible'));
                    }
                });

                $addItem.click(function() {
                    if($max > -1) {
                        var totalRows = $tableObj.find('tbody tr:visible').length;

                        if(totalRows < $max) {
                            addItem();
                        } else {
                            new PNotify({
                                title: $maxErrorTitle,
                                text: $maxErrorMessage,
                                type: 'error'
                            });
                        }
                    } else {
                        addItem();
                    }
                });

                function addItem() {
                    $tableObj.find('tbody').append($tableObj.find('tbody .clonable').clone().show().removeClass('clonable'));

                    html2json($tableObj.find('tbody tr:visible'));
                }

                $('body').on('click', $removeItem, function() {
                    $(this).closest('tr').remove();

                    html2json($tableObj.find('tbody tr:visible'));

                    return false;
                });

                $('body').on('change', $items, function() {
                    html2json($tableObj.find('tbody tr:visible'));
                });

                if($min > -1) {
                    for(var i = 0; i < $min; i++){
                        addItem();
                    }
                }

                if(items != '[]') {
                    var tbl_body = "";
                    var odd_even = false;

                    $.each($.parseJSON(items), function() {
                        addItem();

                        $.each(this, function(k , v) {
                            $tableObj.find('tbody tr:last').find('input[name="item.' + k + '"]').val(v);
                        })
                    });

                    html2json($tableObj.find('tbody tr:visible'));
                }

                function html2json(table) {
                    var json = '[';
                    var otArr = [];
                    var tbl2 = table.each(function(i) {
                        x = $(this).children().closest('td').find('input');
                        var itArr = [];
                        x.each(function() {
                            if(this.value.length > 0) {
                                itArr.push('"' + this.name.replace('item.','') + '":"' + this.value + '"');
                            }
                        });
                        otArr.push('{' + itArr.join(',') + '}');
                    })
                    json += otArr.join(",") + ']'

                    var totalRows = table.length;

                    $field.val( totalRows ? json : null );
                }
            });
        </script>
    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
