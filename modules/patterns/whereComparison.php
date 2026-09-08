<?php

function applyWhereComparisonPatterns(array $tokens): array
{
    global $comparisonWords;

    $words = array_map("strtolower", $tokens);

    /*
    |--------------------------------------------------------------------------
    | IMPLICIT SELECT WITH WHERE
    |--------------------------------------------------------------------------
    |
    | students with age greater than 18
    | students with age greater 18
    | students with age above 18
    | students with age equal to 18
    |
    */

    if (

        count($words) >= 5 &&

        $words[1] == "with"

    ) {

        if (

            isset($comparisonWords[$words[3]])

        ) {

            return [

                "show",

                "*",

                "from",

                $words[0],

                "where",

                $words[2],

                $comparisonWords[$words[3]],

                end($words)

            ];

        }

    }

    return $tokens;
}