{{-- single relationships (1-1, 1-n) --}}
<td>
	<?php
		$relationArray = explode(".", $column['entity']);
		if(count($relationArray) == 1 && $entry->{$column['entity']}) {
            echo $entry->{$column['entity']}->{$column['attribute']};
		}
		else {
            $resultsArray = $crud->getNestedRelationsAttributes($entry, $column['entity'], $column['attribute']);
            if (count($resultsArray)) {
                echo implode(', ', $resultsArray);
            } else {
                echo '-';
            }
		}
	?>
</td>
