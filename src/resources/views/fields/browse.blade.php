<!-- browse server input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
	<input
		type="text"
		class="form-control"
		id="{{ $field['name'] }}-filemanager"

		@foreach ($field as $attribute => $value)
			{{ $attribute }}="{{ $value }}"
		@endforeach
		readonly
	>

	<div class="btn-group" role="group" aria-label="..." style="margin-top: 3px;">
	  <button type="button" data-inputid="{{ $field['name'] }}-filemanager" class="btn btn-default popup_selector">
		<i class="fa fa-cloud-upload"></i> Browse uploads</button>
		<button type="button" data-inputid="{{ $field['name'] }}-filemanager" class="btn btn-default clear_elfinder_picker">
		<i class="fa fa-eraser"></i> Clear</button>
	</div>

  </div>