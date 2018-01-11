{{-- custom return value via attribute --}}
<span>
	<?php
        echo $entry->{$column['function_name']}()->{$column['attribute']};
    ?>
</span>