@php
    $horizontalTabs = $crud->getTabsType() == 'horizontal';
    $columnsWithoutTab = $crud->getElementsWithoutATab($crud->columns());
    $columnsWithTabs = $crud->getUniqueTabNames('columns');
@endphp

@if($columnsWithoutTab->filter(function ($value, $key) { return $value['type'] != 'hidden'; })->count())
    <div class="card">
        {{-- Here is the only case where we might not want to display action columns. That is, when tabs are enabled and there are actually tabs to display — We show them with the tabs! --}}
        @include('crud::inc.show_table', ['columns' => $columnsWithoutTab, 'displayActionsColumn' => !$crud->tabsEnabled() || count($crud->getUniqueTabNames('columns')) === 0])
    </div>
@else
    @include('crud::inc.show_table', ['columns' => $columnsWithoutTab])
@endif

<div class="tab-container {{ $horizontalTabs ? '' : 'container'}} mb-2">

    <div class="nav-tabs-custom {{ $horizontalTabs ? '' : 'row'}}" id="form_tabs">
        <ul class="nav {{ $horizontalTabs ? 'nav-tabs' : 'flex-column nav-pills'}} {{ $horizontalTabs ? '' : 'col-md-3' }}" role="tablist">
            @foreach ($columnsWithTabs as $k => $tabLabel)
                <li role="presentation" class="nav-item">
                    <a href="#tab_{{ Str::slug($tabLabel) }}"
                       aria-controls="tab_{{ Str::slug($tabLabel) }}"
                       role="tab"
                       tab_name="{{ Str::slug($tabLabel) }}"
                       data-toggle="tab"
                       class="nav-link {{ $k === 0 ? 'active' : '' }}"
                    >{{ $tabLabel }}</a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content p-0 {{ $horizontalTabs ? '' : 'col-md-9' }}">
            @foreach ($columnsWithTabs as $k => $tabLabel)
                <div role="tabpanel" class="tab-pane p-0 border-none {{ $k === 0 ? 'active' : '' }}" id="tab_{{ Str::slug($tabLabel) }}">
                    @include('crud::inc.show_table', ['columns' => $crud->getTabItems($tabLabel, 'columns')])
                </div>
            @endforeach

        </div>
    </div>
</div>
