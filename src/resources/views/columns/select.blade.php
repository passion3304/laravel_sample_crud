{{-- single relationships (1-1, 1-n) --}}
<span>
	<?php
		if ($entry->{$column['entity']}) {
	    	echo $entry->{$column['entity']}->{$column['attribute']};
	    }
	?>
</span>
