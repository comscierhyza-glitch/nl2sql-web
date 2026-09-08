<?php

function applyOrderPatterns(array $tokens): array
{
    global $sortingWords;
    global $directionWords;

    $words = array_map("strtolower", $tokens);

    /*
    students sorted by age

    students ordered by lastname

    employees sorted by salary descending
    */

    if (

        count($words) >= 4 &&

        in_array($words[1], $sortingWords) &&

        $words[2] == "by"

    ) {

        $direction = "asc";

        if (

            count($words) >= 5 &&

            isset($directionWords[end($words)])

        ) {

            $direction = $directionWords[end($words)];

        }

        return [

            "show",

            "*",

            "from",

            $words[0],

            "order",

            "by",

            $words[3],

            $direction

        ];

    }

    return $tokens;
}