<!-- dependencyJson -->
  <div class="form-group checklist_dependency"  data-entity ="{{ $field['field_unique_name'] }}">
    <label>{{ $field['label'] }}</label>
    <?php 
      $entity_model = $crud->getModel();

      //short name for dependency fields
      $primary_dependency = $field['dependencies']['primary'];
      $secondary_dependency = $field['dependencies']['secondary'];


      //all items with relation
      $dependencies = $primary_dependency['model']::with($primary_dependency['entity_secondary'])->get();

      $dependencyArray = [];

      //convert dependency array to simple matrix ( prymary id as key and array with secondaries id )
      foreach($dependencies as $primary){
          $dependencyArray[$primary->id] = [];
        foreach($primary->{$primary_dependency['entity_secondary']} as $secondary){
            $dependencyArray[$primary->id][] = $secondary->id;
        }
      }

      //for update form, get initial state of the entity
      if( isset($id) && $id ){
        
        //get entity with relations for primary dependency
        $entity_dependencies = $entity_model->with($primary_dependency['entity'])
          ->with($primary_dependency['entity'].'.'.$primary_dependency['entity_secondary'])
          ->where('id', $id)
          ->first();

        $secondaries_from_primary = [];
        
        //convert relation in array 
        $primary_array = $entity_dependencies->{$primary_dependency['entity']}->toArray();
        
        $secondary_ids = [];
        
        //create secondary dependency from primary relation, used to check what chekbox must be check from second checklist
        if( old($primary_dependency['name']) ) {
          foreach( old($primary_dependency['name']) as $primary_item ){
            foreach($dependencyArray[$primary_item] as $second_item ){
                $secondary_ids[$second_item] = $second_item;
            }
          }
        }else{ //create dependecies from relation if not from validate error
          foreach( $primary_array as $primary_item ){
            foreach($primary_item[$secondary_dependency['entity']] as $second_item ){
                $secondary_ids[$second_item['id']] = $second_item['id'];
            }
          }
        }

      }

      //json encode of dependency matrix
      $dependencyJson = json_encode($dependencyArray);
    ?>
    <script>
     var  {{ $field['field_unique_name'] }} = {!! $dependencyJson !!};
    </script>
    <div class="row" >
        <div class="col-xs-12">
           <label>{{ $primary_dependency['label'] }}</label>
        </div>
        
        <div class="hidden_fields_primary" data-name = "{{ $primary_dependency['name'] }}">
          @if(isset($field['value']))
            @if(old($primary_dependency['name']))
              @foreach( old($primary_dependency['name']) as $item )
                <input type="hidden" class="primary_hidden" name="{{ $primary_dependency['name'] }}[]" value="{{ $item }}">
              @endforeach
            @else
              @foreach( $field['value'][0]->lists('id', 'id')->toArray() as $item )
                <input type="hidden" class="primary_hidden" name="{{ $primary_dependency['name'] }}[]" value="{{ $item }}">
              @endforeach
            @endif
          @endif
        </div>
    
        @foreach ($primary_dependency['model']::all() as $connected_entity_entry)
            <div class="col-sm-{{ isset($primary_dependency['number_columns']) ? intval(12/$primary_dependency['number_columns']) : '4'}}">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                        data-id = "{{ $connected_entity_entry->id }}"
                        class = 'primary_list'
                        @foreach ($primary_dependency as $attribute => $value)
                            @if (is_string($attribute) && $attribute != 'value')
                              @if ($attribute=='name')
                                {{ $attribute }}="{{ $value }}_show[]"
                              @else
                                {{ $attribute }}="{{ $value }}"
                              @endif
                            @endif
                        @endforeach
                         value="{{ $connected_entity_entry->id }}"

                         @if( ( isset($field['value']) && is_array($field['value']) && in_array($connected_entity_entry->id, $field['value'][0]->lists('id', 'id')->toArray())) || ( old($primary_dependency["name"]) && in_array($connected_entity_entry->id, old( $primary_dependency["name"])) ) )
                               checked = "checked" 
                        @endif >
                        {{ $connected_entity_entry->{$primary_dependency['attribute']} }}

                  </label>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-xs-12">
          <label>{{ $secondary_dependency['label'] }}</label>
        </div>
        
        <div class="hidden_fields_secondary" data-name="{{ $secondary_dependency['name'] }}">
          @if(isset($field['value']))
            @if(old($secondary_dependency['name']))
              @foreach( old($secondary_dependency['name']) as $item )
                <input type="hidden" class="secondary_hidden" name="{{ $secondary_dependency['name'] }}[]" value="{{ $item }}">
              @endforeach
            @else
              @foreach( $field['value'][1]->lists('id', 'id')->toArray() as $item )
                <input type="hidden" class="secondary_hidden" name="{{ $secondary_dependency['name'] }}[]" value="{{ $item }}">
              @endforeach
            @endif
          @endif
        </div>

        @foreach ($secondary_dependency['model']::all() as $connected_entity_entry)
            <div class="col-sm-{{ isset($secondary_dependency['number_columns']) ? intval(12/$secondary_dependency['number_columns']) : '4'}}">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                        class = 'secondary_list'
                        data-id = "{{ $connected_entity_entry->id }}"
                        @foreach ($secondary_dependency as $attribute => $value)
                            @if (is_string($attribute) && $attribute != 'value')
                              @if ($attribute=='name')
                                {{ $attribute }}="{{ $value }}_show[]"
                              @else
                                {{ $attribute }}="{{ $value }}"
                              @endif
                            @endif
                        @endforeach
                         value="{{ $connected_entity_entry->id }}"

                        @if( ( isset($field['value']) && is_array($field['value']) && (  in_array($connected_entity_entry->id, $field['value'][1]->lists('id', 'id')->toArray()) || isset( $secondary_ids[$connected_entity_entry->id])) || ( old($secondary_dependency['name']) &&   in_array($connected_entity_entry->id, old($secondary_dependency['name'])) )))
                             checked = "checked"
                             @if(isset( $secondary_ids[$connected_entity_entry->id]))
                              disabled = disabled
                             @endif
                        @endif > {{ $connected_entity_entry->{$secondary_dependency['attribute']} }}
                  </label>
                </div>
            </div>
        @endforeach
    </div>

  </div>