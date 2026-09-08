<?php

function applyWhereAgePatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    /*
    |--------------------------------------------------------------------------
    | AGE INFERENCE
    |--------------------------------------------------------------------------
    |
    | students older than 18
    | employees younger than 25
    |
    */

    if (

        count($words) >= 3 &&

        in_array($words[1], ["older", "younger"])

    ) {

        $operator = $words[1] == "older" ? ">" : "<";

        return [

            "show",

            "*",

            "from",

            $words[0],

            "where",

            "age",

            $operator,

            end($words)

        ];

    }

    return $tokens;
}