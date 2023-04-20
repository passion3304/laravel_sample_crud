@php
    $horizontalTabs = $crud->getTabsType()=='horizontal' ? true : false;
@endphp

@if ($crud->getFieldsOrColumnsWithoutATab()->filter(function ($value, $key) { return $value['type'] != 'hidden'; })->count())
    <div class="card">
        <div class="card-body">
            @include('crud::inc.show_tabbed_table', ['columns' => $crud->getFieldsOrColumnsWithoutATab()])
        </div>
    </div>
@else
    @include('crud::inc.show_tabbed_table', ['columns' => $crud->getFieldsOrColumnsWithoutATab()])
@endif

<div class="tab-container {{ $horizontalTabs ? '' : 'container'}} mb-2">

    <div class="nav-tabs-custom {{ $horizontalTabs ? '' : 'row'}}" id="form_tabs">
        <ul class="nav {{ $horizontalTabs ? 'nav-tabs' : 'flex-column nav-pills'}} {{ $horizontalTabs ? '' : 'col-md-3' }}" role="tablist">
            @foreach ($crud->getTabs() as $k => $tab)
                <li role="presentation" class="nav-item">
                    <a href="#tab_{{ Str::slug($tab) }}"
                       aria-controls="tab_{{ Str::slug($tab) }}"
                       role="tab"
                       tab_name="{{ Str::slug($tab) }}"
                       data-toggle="tab"
                       class="nav-link {{ $k === 0 ? 'active' : '' }}"
                    >{{ $tab }}</a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content p-0 {{ $horizontalTabs ? '' : 'col-md-9' }}">
            @foreach ($crud->getTabs() as $k => $tab)
                <div role="tabpanel" class="tab-pane {{ $k === 0 ? 'active' : '' }}" id="tab_{{ Str::slug($tab) }}">
                    @include('crud::inc.show_tabbed_table', ['columns' => $crud->getTabFieldsOrColumns($tab)])
                </div>
            @endforeach

        </div>
    </div>
</div>
