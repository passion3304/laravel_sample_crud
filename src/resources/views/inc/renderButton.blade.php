@if ($button->type == 'model_function')
	{!! $entry->{$button->content}(); !!}
@else
	@include($button->content)
@endif