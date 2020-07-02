<?php

Blade::directive('loadCssOnce', function ($parameter) {

    // if parameter starts with '$' we assume it's a php variable.
    $parameterIsVariable = substr($parameter, 0, 1) === '$' ? true : false;

    if ($parameterIsVariable) {
        return "<?php
            if(! Assets::isAssetLoaded({$parameter})) {
                Assets::markAssetAsLoaded({$parameter});
                echo Assets::echoCssFileLink({$parameter});
            }
        ?>";
    } else {
        $path = trim($parameter, '"');
        $path = trim($parameter, "'");

        if (! Assets::isAssetLoaded($path)) {
            // remember this file path, so that it's never loaded again
            Assets::markAssetAsLoaded($path);

            // load the CSS file
            return '<link href="'.asset($path).'" rel="stylesheet" type="text/css" />';
        }
    }
});

Blade::directive('loadJsOnce', function ($parameter) {

    // if parameter starts with '$' we assume it's a php variable.
    $parameterIsVariable = substr($parameter, 0, 1) === '$' ? true : false;

    if ($parameterIsVariable) {
        return "<?php
            if(! Assets::isAssetLoaded({$parameter})) {
                Assets::markAssetAsLoaded({$parameter});
                echo Assets::echoJsScript({$parameter});
            }
        ?>";
    } else {
        $path = trim($parameter, '"');
        $path = trim($parameter, "'");

        if (! Assets::isAssetLoaded($path)) {
            // remember this file path, so that it's never loaded again
            Assets::markAssetAsLoaded($path);
            // load the JS file
            return '<script src="'.asset($path).'"></script>';
        }
    }
});

Blade::directive('loadOnce', function ($parameter) {
    $path = trim($parameter, '"');
    $path = trim($parameter, "'");
    $exists = Assets::isAssetLoaded($path);

    if ($exists) {
        return '<?php if (false) { ?>';
    } else {
        Assets::markAssetAsLoaded($path);

        return '<?php if (true) { ?>';
    }
});

Blade::directive('endLoadOnce', function () {
    return '<?php } ?>';
});
