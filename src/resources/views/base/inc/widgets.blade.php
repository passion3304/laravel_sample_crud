@if (!empty($widgets))
	@foreach ($widgets as $currentWidget)

		@if (is_array($currentWidget))
			@include($currentWidget['viewNamespace'] ?? config('backpack.base.component_view_namespaces.widgets')[0] .'.'. $currentWidget['type'], ['widget' => $currentWidget])
		@else
			@include($currentWidget->getFinalViewPath(), ['widget' => $currentWidget->toArray()])
		@endif

	@endforeach
@endif
