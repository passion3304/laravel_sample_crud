<div class="container m-t-10 m-b-10">
		<div class="row">
			<div class="col-md-12">

				@if (count($translations))
					<p>
						Translations of this {{ $crud->entity_name }}:
					</p>
					<table class="table table-condensed table-bordered" style="m-t-10">
						<thead>
				          <tr>
				          	<th>
				          		Language
				          	</th>
				          	{{-- Table columns --}}
				            @foreach ($crud->columns as $column)
				              <th>{{ $column['label'] }}</th>
				            @endforeach

				            @if ( !( isset($crud->edit_permission) && $crud->edit_permission === false && isset($crud->delete_permission) && $crud->delete_permission === false ) )
				              <th>{{ trans('backpack::crud.actions') }}</th>
				            @endif
				          </tr>
					    </thead>
					    <tbody>
				    		@foreach ($translations as $key => $entry)
				    			<tr>
				    				<td>
				    					{{ $entry->language->name }}
				    				</td>

				    				@foreach ($crud->columns as $column)
				                      @if (isset($column['type']) && $column['type']=='select_multiple')
				                        {{-- relationships with pivot table (n-n) --}}
				                        <td><?php
				                        $results = $entry->{$column['entity']}()->getResults();
				                        if ($results && $results->count()) {
				                            $results_array = $results->lists($column['attribute'], 'id');
				                            echo implode(', ', $results_array->toArray());
				                          }
				                          else
				                          {
				                            echo '-';
				                          }
				                         ?></td>
				                      @elseif (isset($column['type']) && $column['type']=='select')
				                        {{-- single relationships (1-1, 1-n) --}}
				                        <td><?php
				                        if ($entry->{$column['entity']}()->getResults()) {
				                            echo $entry->{$column['entity']}()->getResults()->{$column['attribute']};
				                          }
				                         ?></td>
				                      @else
				                        {{-- regular object attribute --}}
				                        <td>{{ str_limit(strip_tags($entry->$column['name']), 80, "[...]") }}</td>
				                      @endif

				                    @endforeach

				                    @if ( !( isset($crud->edit_permission) && $crud->edit_permission === false && isset($crud->delete_permission) && $crud->delete_permission === false ) )
				                    <td>
				                      {{-- <a href="{{ Request::url().'/'.$entry->id }}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> {{ trans('backpack::crud.preview') }}</a> --}}
				                      @if (!(isset($crud->edit_permission) && !$crud->edit_permission))
				                        <a href="{{ str_replace($original_entry->id, $entry->id, str_replace('details', 'edit', Request::url())) }}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {{ trans('backpack::crud.edit') }}</a>
				                      @endif
				                       @if (!(isset($crud->delete_permission) && !$crud->delete_permission))
				                      <a href="{{ str_replace($original_entry->id, $entry->id, str_replace('details', '', Request::url())) }}" class="btn btn-xs btn-default" data-button-type="delete"><i class="fa fa-trash"></i> {{ trans('backpack::crud.delete') }}</a>
				                      @endif
				                    </td>
				                    @endif
				    			</tr>
							@endforeach
						</tbody>
					</table>
				@else
					No translations available. <br><br>
				@endif

				@if ($languages_to_translate_in->count())
				Add translation to:
					@foreach($languages_to_translate_in as $lang)
						<a class="btn btn-xs btn-default" href="{{ str_replace('details', 'translate/'.$lang->abbr, Request::url()) }}"><i class="fa fa-plus"></i> {{ $lang->name }}</a>
					@endforeach
				@endif
			</div>
		</div>
	</div>
