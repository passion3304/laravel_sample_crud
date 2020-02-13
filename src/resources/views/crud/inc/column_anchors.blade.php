@php
        $column['anchor']['href'] = is_callable($column['anchor']['href']) ? $column['anchor']['href']($crud, $column, $entry) : $column['anchor']['href'];
        $column['anchor']['class'] = isset($column['anchor']['class']) ? (is_callable($column['anchor']['class']) ? $column['anchor']['class']($crud, $column, $entry) : ($column['anchor']['class'] ?? '')) : '';
        $column['anchor']['target'] = $column['anchor']['target'] ?? '';

        echo '<a href="'.$column['anchor']['href'].'" target="'.$column['anchor']['target'].'" class="'.$column['anchor']['class'].'">'. $text .'</a>';
@endphp
