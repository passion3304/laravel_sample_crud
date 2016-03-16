<!-- PAGE OR LINK field -->
<!-- Used in Dick\MenuItemManager -->

<?php
    $field['options'] = ['page_link' => 'Page link', 'internal_link' => 'Internal link', 'external_link' => 'External link'];
    $field['allows_null'] = false;
    $active_pages = \App\Models\Page::all();
?>

<div class="form-group">
    <label>{{ $field['label'] }}</label>
    <div class="clearfix"></div>

    <div class="col-sm-3">
        <select
            class="form-control"
            name="type"
            id="page_or_link_select"

            @foreach ($field as $attribute => $value)
                @if (!is_array($value))
                {{ $attribute }}="{{ $value }}"
                @endif
            @endforeach
            >

            @if (isset($field['allows_null']) && $field['allows_null']==true)
                <option value="">-</option>
            @endif

                @if (count($field['options']))
                    @foreach ($field['options'] as $key => $value)
                        <option value="{{ $key }}"
                            @if (isset($field['value']) && $key==$field['value'])
                                 selected
                            @endif
                        >{{ $value }}</option>
                    @endforeach
                @endif
        </select>
    </div>
    <div class="col-sm-9">
        <!-- external link input -->
          <div class="page_or_link_value <?php if (!isset($entry) || $entry->type != 'external_link') { echo 'hidden'; } ?>" id="page_or_link_external_link">
            <input
                type="url"
                class="form-control"
                name="link"
                placeholder="http://example.com/your-desired-page"

                @if (!isset($entry) || $entry->type!='external_link')
                    disabled="disabled"
                  @endif

                @if (isset($entry) && $entry->type=='external_link' && isset($entry->link) && $entry->link!='')
                    value="{{ $entry->link }}"
                @endif
                >
          </div>
          <!-- internal link input -->
          <div class="page_or_link_value <?php if (!isset($entry) || $entry->type != 'internal_link') { echo 'hidden'; } ?>" id="page_or_link_internal_link">
            <input
                type="text"
                class="form-control"
                name="link"
                placeholder="Internal slug. Ex: 'admin/page' (no quotes) for '{{ url('admin/page') }}'"

                @if (!isset($entry) || $entry->type!='internal_link')
                    disabled="disabled"
                  @endif

                @if (isset($entry) && $entry->type=='internal_link' && isset($entry->link) && $entry->link!='')
                    value="{{ $entry->link }}"
                @endif
                >
          </div>
          <!-- page slug input -->
          <div class="page_or_link_value <?php if (isset($entry) && $entry->type != 'page_link') { echo 'hidden'; } ?>" id="page_or_link_page">
            <select
                class="form-control"
                name="page_id"
                >
                @if (!count($active_pages))
                    <option value="">-</option>
                @else
                    @foreach ($active_pages as $key => $page)
                        <option value="{{ $page->id }}"
                            @if (isset($entry) && isset($entry->page_id) && $page->id==$entry->page_id)
                                 selected
                            @endif
                        >{{ $page->name }}</option>
                    @endforeach
                @endif

            </select>
          </div>
    </div>
    <div class="clearfix"></div>

</div>
