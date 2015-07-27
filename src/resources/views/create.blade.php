@extends('admin.layout')

@section('content-header')
	<section class="content-header">
	  <h1>
	    {{ trans('crud.add') }} <span class="text-lowercase">{{ $crud['entity_name'] }}</span>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="{{ url('admin/dashboard') }}">Admin</a></li>
	    <li><a href="{{ url($crud['route']) }}" class="text-capitalize">{{ $crud['entity_name_plural'] }}</a></li>
	    <li class="active">{{ trans('crud.add') }}</li>
	  </ol>
	</section>
@endsection

@section('content')
<div class="row">
	<div class="col-md-8 col-md-offset-2">
		<!-- Default box -->
		@if (!(isset($crud['view_table_permission']) && !$crud['view_table_permission']))
			<a href="{{ url($crud['route']) }}"><i class="fa fa-angle-double-left"></i> {{ trans('crud.back_to_all') }} <span class="text-lowercase">{{ $crud['entity_name_plural'] }}</span></a><br><br>
		@endif

		  {!! Form::open(array('url' => $crud['route'], 'method' => 'post')) !!}
		  <div class="box">

		    <div class="box-header with-border">
		      <h3 class="box-title">{{ trans('crud.add_a_new') }} {{ $crud['entity_name'] }}</h3>
		    </div>
		    <div class="box-body">
		      @include('crud/form_content')
		    </div><!-- /.box-body -->
		    <div class="box-footer">
		    	<div class="form-group">
		    	  <span>{{ trans('crud.after_saving') }}:</span>
		          <div class="radio">
		            <label>
		              <input type="radio" name="redirect_after_save" value="{{ $crud['route'] }}" checked="">
		              {{ trans('crud.go_to_the_table_view') }}
		            </label>
		          </div>
		          <div class="radio">
		            <label>
		              <input type="radio" name="redirect_after_save" value="{{ $crud['route'].'/create' }}">
		              {{ trans('crud.let_me_add_another_item') }}
		            </label>
		          </div>
		          <div class="radio">
		            <label>
		              <input type="radio" name="redirect_after_save" value="current_item_edit">
		              {{ trans('crud.edit_the_new_item') }}
		            </label>
		          </div>
		        </div>

			  <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
			  <button type="submit" class="btn btn-success ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-save"></i> {{ trans('crud.add') }}</span></button>
		      <a href="{{ url($crud['route']) }}" class="btn btn-default ladda-button" data-style="zoom-in"><span class="ladda-label">{{ trans('crud.cancel') }}</span></a>
		    </div><!-- /.box-footer-->

		  </div><!-- /.box -->
		  {!! Form::close() !!}
	</div>
</div>

@endsection
