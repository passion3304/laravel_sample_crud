<!-- view field -->

<div @include('crud::inc.field_wrapper_attributes') >
  @include($field['view'], ['crud' => $crud, 'entry' => $entry ?? '', 'field' => $field])
</div>
