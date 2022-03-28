<?php

use Illuminate\Support\Str;



/**
 * This macro adds the ability to convert a dot.notation string into a [braket][notation] with some special
 * options that helps us in our usecases.
 *
 * - $ignore: usefull when you want to convert a laravel validator rule for nested items and you
 *   would like to ignore the `*` element from the string.
 *
 * - $keyFirst: when true, we will use the first part of the string as key and only bracket the remaining elements.
 *   eg: `address.street`
 *      - when true: `address[street]`
 *      - when false: `[address][street]`
 */
if (! Str::hasMacro('dotsToSquareBrackets')) {
    Str::macro('dotsToSquareBrackets', function ($string, $ignore = [], $keyFirst = true) {
        $stringParts = explode('.', $string);
        $result = '';

        foreach ($stringParts as $key => $part) {
            if (in_array($part, $ignore)) {
                continue;
            }
            $result .= ($key === 0 && $keyFirst) ? $part : '['.$part.']';
        }

        return $result;
    });
}