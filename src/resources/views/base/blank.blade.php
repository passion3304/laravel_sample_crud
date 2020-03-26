@extends(backpack_view('layouts.top_left'))

@php
	// Merge widgets that were fluently declared with widgets declared without the fluent syntax: 
	// - $data['widgets']['before_content']
	// - $data['widgets']['after_content']
	if (isset($widgets)) {
		foreach ($widgets as $group => $widgetGroup) {
			foreach ($widgetGroup as $key => $widget) {
				\Backpack\CRUD\app\Library\Widget::add($widget)->group($group);
			}
		}
	}
@endphp

@section('before_content_widgets')
	@include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('group', 'before_content')->toArray() ])
@endsection

@section('content')
@endsection

@section('after_content_widgets')
	@if (isset($widgets['after_content']))
		@include(backpack_view('inc.widgets'), [ 'widgets' => app('widgets')->where('group', 'after_content')->toArray() ])
	@endif
@endsection