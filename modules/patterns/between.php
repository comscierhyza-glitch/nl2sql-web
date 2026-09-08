<?php

function applyBetweenPatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    /*
    |--------------------------------------------------------------------------
    | BETWEEN
    |--------------------------------------------------------------------------
    |
    | students age between 18 and 25
    | employees salary between 30000 and 50000
    |
    */

    if (

        count($words) >= 6 &&

        $words[2] == "between"

    ) {

        return [

            "show",

            "*",

            "from",

            $words[0],

            "where",

            $words[1],

            "between",

            $words[3],

            "and",

            $words[5]

        ];

    }

    return $tokens;
}