<div class="box box-solid m-b-10">
  <!-- /.box-header -->
  <div class="box-body bg-light-gray">
    <form role="form" class="{{ ($stack=='top' || $stack == 'bottom')?'form-inline':'form' }}">
      @if ($crud->filters->where('stack', $stack)->count())
        @foreach ($crud->filters->where('stack', $stack) as $filter)
          @include($filter->view)
        @endforeach
      @endif
    </form>
  </div>
  <!-- /.box-body -->
</div>