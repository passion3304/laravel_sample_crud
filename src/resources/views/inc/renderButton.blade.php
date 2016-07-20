@if ($button->type == 'model_function')
	{!! $entry->{$function_name}(); !!}
@else
	@include($button->content)
@endif