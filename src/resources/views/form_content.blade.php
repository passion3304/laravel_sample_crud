<form role="form">
    {{-- Show the erros, if any --}}
    @if ($errors->any())
        <div class="col-md-12">
            <div class="callout callout-danger">
                <h4>{{ trans('backpack::crud.please_fix') }}</h4>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

  @if ($crud->model->translationEnabled())
    <input type="hidden" name="locale" value={{ $crud->request->input('locale')?$crud->request->input('locale'):App::getLocale() }}>
  @endif

    {{-- See if we're using tabs --}}

    @if ($crud->tabsEnabled())

        @php
        $horizontalTabs = $crud->getTabsType()=='horizontal' ? true : false;
        @endphp

        @push('crud_fields_styles')
            <style>
                .nav-tabs-custom {
                    box-shadow: none;
                }
                .nav-tabs-custom > .nav-tabs.nav-stacked > li {
                    margin-right: 0;
                }

                .tab-pane .form-group h1:first-child,
                .tab-pane .form-group h2:first-child,
                .tab-pane .form-group h3:first-child {
                    margin-top: 0;
                }
            </style>
        @endpush

        <div class="tab-container {{ $horizontalTabs ? 'col-md-12' : 'col-md-3 m-t-10' }}">

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs {{!$horizontalTabs ? ' nav-stacked' : ''}}" role="tablist">
                    @foreach ($crud->getTabs() as $k => $tab)
                        <li role="presentation" class="{{$k == 0 ? 'active' : ''}}">
                            <a href="#tab_{{ camel_case($tab) }}" aria-controls="tab_{{ camel_case($tab) }}" role="tab" data-toggle="tab">{{ $tab }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>

        <div class="tab-content {{$horizontalTabs ? 'col-md-12' : 'col-md-9 m-t-10'}}">

            @foreach ($crud->getTabs() as $k => $tab)
            <div role="tabpanel" class="tab-pane{{$k == 0 ? ' active' : ''}}" id="tab_{{ camel_case($tab) }}">

                {{-- Show the inputs --}}
                @foreach ($crud->getTabFields($tab) as $field)
                    <!-- load the view from the application if it exists, otherwise load the one in the package -->
                    @if(view()->exists('vendor.backpack.crud.fields.'.$field['type']))
                        @include('vendor.backpack.crud.fields.'.$field['type'], array('field' => $field))
                    @else
                        @include('crud::fields.'.$field['type'], array('field' => $field))
                    @endif
                @endforeach

            </div>
            @endforeach

        </div>
    @else

        {{-- Show the inputs --}}
        @foreach ($fields as $field)
            <!-- load the view from the application if it exists, otherwise load the one in the package -->
            @if(view()->exists('vendor.backpack.crud.fields.'.$field['type']))
                @include('vendor.backpack.crud.fields.'.$field['type'], array('field' => $field))
            @else
                @include('crud::fields.'.$field['type'], array('field' => $field))
            @endif
        @endforeach

    @endif
</form>

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@section('after_styles')
    <!-- CRUD FORM CONTENT - crud_fields_styles stack -->
    @stack('crud_fields_styles')
@endsection

@section('after_scripts')
    <!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
    @stack('crud_fields_scripts')

    <script>
    jQuery('document').ready(function($){

        // Ctrl+S and Cmd+S trigger Save button click
        $(document).keydown(function(e) {
            if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey))
            {
                e.preventDefault();
                // alert("Ctrl-s pressed");
                $("button[type=submit]").trigger('click');
                return false;
            }
            return true;
        });

        @if( $crud->autoFocusOnFirstField )
        //Focus on first field
        @php
        $focusField = array_first($fields, function($field){
            return isset($field['auto_focus']) && $field['auto_focus'] == true;
        })
        @endphp

        @if($focusField)
        window.focusField = $('[name="{{$focusField['name']}}"]').eq(0),
        @else
        var focusField = $('form').find('input, textarea, select').not('[type="hidden"]').eq(0),
        @endif

        fieldOffset = focusField.offset().top,
        scrollTolerance = $(window).height() / 2;

        focusField.trigger('focus');

        if( fieldOffset > scrollTolerance ){
            $('html, body').animate({scrollTop: (fieldOffset - 30)});
        }
        @endif
    });
    </script>
@endsection
