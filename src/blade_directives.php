<?php

$already_loaded_assets = [];

Blade::directive('loadCssOnce', function ($parameter) use (&$already_loaded_assets) {
    $path = trim($parameter, '"');
    $path = trim($parameter, "'");

    if (! in_array($path, $already_loaded_assets)) {
        // remember this file path, so that it's never loaded again
        array_push($already_loaded_assets, $path);
        // load the CSS file
        return '<link href="'.asset($path).'" rel="stylesheet" type="text/css" />';
    }
});

Blade::directive('loadJsOnce', function ($parameter) use (&$already_loaded_assets) {
    $path = trim($parameter, '"');
    $path = trim($parameter, "'");

    if (! in_array($path, $already_loaded_assets)) {
        // remember this file path, so that it's never loaded again
        array_push($already_loaded_assets, $path);
        // load the JS file
        return '<script src="'.asset($path).'"></script>';
    }
});

Blade::directive('loadOnce', function ($parameter) use (&$already_loaded_assets) {
    $path = trim($parameter, '"');
    $path = trim($parameter, "'");
    $exists = in_array($path, $already_loaded_assets);

    if ($exists) {
        return '<?php if (false) { ?>';
    } else {
        array_push($already_loaded_assets, $path);

        return '<?php if (true) { ?>';
    }
});

Blade::directive('endLoadOnce', function () {
    return '<?php } ?>';
});
