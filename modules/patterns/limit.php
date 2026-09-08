<?php

function applyLimitPatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    /*
    show 5 students

    show first 5 students

    list first 10 employees

    display first 20 products
    */

    // show first 5 students

    if (

        count($words) == 4 &&

        in_array($words[0], ["show", "list", "display"]) &&

        $words[1] == "first" &&

        is_numeric($words[2])

    ) {

        return [

            "show",

            "*",

            "from",

            $words[3],

            "limit",

            $words[2]

        ];

    }

    // show 5 students

    if (

        count($words) == 3 &&

        in_array($words[0], ["show", "list", "display"]) &&

        is_numeric($words[1])

    ) {

        return [

            "show",

            "*",

            "from",

            $words[2],

            "limit",

            $words[1]

        ];

    }

    return $tokens;
}