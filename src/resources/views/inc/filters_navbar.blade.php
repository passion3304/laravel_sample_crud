<nav class="navbar navbar-default navbar-filters">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
          <span class="sr-only">Toggle filters</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Filters</a>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          <li class="active"><a href="#">Trashed <span class="sr-only">(current)</span></a></li>
          <li><a href="#">Active</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Select <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="#">Action</a></li>
              <li><a href="#">Another action</a></li>
              <li><a href="#">Something else here</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="#">Separated link</a></li>
              <li role="separator" class="divider"></li>
              <li><a href="#">One more separated link</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Select2 <span class="caret"></span></a>
            <div class="dropdown-menu padding-10">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Modi suscipit tempora dolores! Aliquid qui fugiat repellendus. Ex ut blanditiis, eveniet vero natus laboriosam, porro, harum magnam nihil qui, ea perspiciatis.
            </div>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Custom <span class="caret"></span></a>
            <div class="dropdown-menu padding-10">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Modi suscipit tempora dolores! Aliquid qui fugiat repellendus. Ex ut blanditiis, eveniet vero natus laboriosam, porro, harum magnam nihil qui, ea perspiciatis.
            </div>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li><a href="#"><i class="fa fa-ban"></i> Clear filters</a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>


  @if ($crud->filters->count())
    <!-- TOP FILTERS STACK -->
    <div class="row">
      <div class="col-md-12 text-center">
		@if ($crud->filters->count())
			@foreach ($crud->filters as $filter)
				@include($filter->view)
			@endforeach
		@endif
      </div>
    </div>
  @endif


@push('crud_list_styles')
	<style>
    .backpack-filter label {
      color: #868686;
      font-weight: 600;
      text-transform: uppercase;
    }

    .navbar-filters {
      min-height: 25px;
      border-radius: 0;
      margin-bottom: 10px;
      background: #f9f9f9;
      border-color: #f4f4f4;
    }

    .navbar-filters .navbar-toggle {
      padding: 10px 15px;
      border-radius: 0;
    }

    .navbar-filters .navbar-brand {
      height: 25px;
      padding: 5px 15px;
      font-size: 14px;
      text-transform: uppercase;
    }
    @media (min-width: 768px) {
      .navbar-nav>li>a {
          padding-top: 5px;
          padding-bottom: 5px;
      }
    }
    </style>
@endpush