<?php

function detectAliases($tokens)
{
    $aliases = [];

    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {

        if (
            strtolower($tokens[$i]) == "as" &&
            isset($tokens[$i - 1]) &&
            isset($tokens[$i + 1])
        ) {

            $aliases[] = [

                "position" => $i,

                "target"   => $tokens[$i - 1],

                "alias"    => $tokens[$i + 1]

            ];
        }
    }

    return $aliases;
}