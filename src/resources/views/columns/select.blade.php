{{-- single relationships (1-1, 1-n) --}}
<td>
    <?php
        $attributes = $crud->getModelAttributeFromRelation($entry, $column['entity'], $column['attribute']);
        if (count($attributes)) {
            echo implode(', ', $attributes);
        } else {
            echo '-';
        }
    ?>
</td>
