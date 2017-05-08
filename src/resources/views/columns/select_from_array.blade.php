{{-- single relationships (1-1, 1-n) --}}
<td>
	<?php
		if ($entry->{$column['name']} !== null) {
	    	echo $column['options'][$entry->{$column['name']}];
	    } else {
	    	echo "NULL";
	    }
	?>
</td>
