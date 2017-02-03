{{-- relationships with pivot table (n-n) --}}
<td>
    <?php
        $results = $entry->{$column['entity']};
        $primary_key = (new $column['model'])->getKeyName();

        if ($results && $results->count()) {
            $results_array = $results->pluck($column['attribute'], $primary_key);
            echo implode(', ', $results_array->toArray());
        } else {
            echo '-';
        }
    ?>
</td>
