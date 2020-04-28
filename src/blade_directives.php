<?php

$crudPanel = app('crud');

Blade::directive('loadCssOnce', function ($parameter) use ($crudPanel) {

    // if parameter starts with '$' we assume it's a php variable.
    $parameterIsVariable = substr($parameter, 0, 1) === '$' ? true : false;

    if ($parameterIsVariable) {
        return '<?php

        if(!$crud->isAssetLoaded('.$parameter.')) {
            $crud->markAssetAsLoaded('.$parameter.');
            echo $crud->echoCssFileLink('.$parameter.');
        }
    ?>';
    } else {
        $path = trim($parameter, '"');
        $path = trim($parameter, "'");

        if (! in_array($path, $crudPanel->getLoadedAssets())) {
            // remember this file path, so that it's never loaded again
            $crudPanel->markAssetAsLoaded($path);

            // load the CSS file
            return '<link href="'.asset($path).'" rel="stylesheet" type="text/css" />';
        }
    }
});

Blade::directive('loadJsOnce', function ($parameter) use ($crudPanel) {

    // if parameter starts with '$' we assume it's a php variable.
    $parameterIsVariable = substr($parameter, 0, 1) === '$' ? true : false;

    if ($parameterIsVariable) {
        return '<?php

            if(!$crud->isAssetLoaded('.$parameter.')) {
                $crud->markAssetAsLoaded('.$parameter.');
                echo $crud->echoJsScript('.$parameter.');
            }
        ?>';
    } else {
        $path = trim($parameter, '"');
        $path = trim($parameter, "'");

        if (! in_array($path, $crudPanel->getLoadedAssets())) {
            // remember this file path, so that it's never loaded again
            $crudPanel->markAssetAsLoaded($path);
            // load the JS file
            return '<script src="'.asset($path).'"></script>';
        }
    }
});

Blade::directive('loadOnce', function ($parameter) use ($crudPanel) {
    $path = trim($parameter, '"');
    $path = trim($parameter, "'");
    $exists = in_array($path, $crudPanel->getLoadedAssets());

    if ($exists) {
        return '<?php if (false) { ?>';
    } else {
        $crudPanel->markAssetAsLoaded($path);

        return '<?php if (true) { ?>';
    }
});

Blade::directive('endLoadOnce', function () {
    return '<?php } ?>';
});
