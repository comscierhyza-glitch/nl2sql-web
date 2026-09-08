<?php

function applyWhereContextPatterns(array $tokens): array
{
    global $contextInference;

    $words = array_map("strtolower", $tokens);

    /*
    |--------------------------------------------------------------------------
    | CONTEXT-AWARE ATTRIBUTE INFERENCE
    |--------------------------------------------------------------------------
    |
    | students named Juan
    | employees called Pedro
    | students in BSCS
    | students from Ipil
    |
    */

    if (count($words) >= 3) {

        $table = $words[0];
        $keyword = $words[1];

        if (

            isset($contextInference[$table]) &&
            isset($contextInference[$table][$keyword])

        ) {

            return [

                "show",

                "*",

                "from",

                $table,

                "where",

                $contextInference[$table][$keyword],

                "=",

                end($tokens)

            ];

        }

    }

    return $tokens;
}