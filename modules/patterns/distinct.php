<?php

function applyDistinctPatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    /*
    ---------------------------------------------------------
    show distinct departments
    ---------------------------------------------------------
    */

    if (
        count($words) == 3 &&
        $words[0] == "show" &&
        $words[1] == "distinct"
    ) {

        return [

            "show",

            "distinct",

            $words[2]

        ];
    }

    /*
    ---------------------------------------------------------
    show distinct course from students
    ---------------------------------------------------------
    */

    if (
        count($words) >= 5 &&
        $words[0] == "show" &&
        $words[1] == "distinct" &&
        $words[3] == "from"
    ) {

        return $tokens;
    }

    return $tokens;
}