<?php

function applySelectPatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    // show all students

    if (
        count($words) == 3 &&
        in_array($words[0], ["show", "list", "display"]) &&
        $words[1] == "all"
    ) {

        return [

            "show",

            "*",

            "from",

            $words[2]

        ];

    }

    // show students

    if (

        count($words) == 2 &&

        in_array($words[0], ["show", "list", "display"])

    ) {

        return [

            "show",

            "*",

            "from",

            $words[1]

        ];

    }

    // students

    if (count($words) == 1) {

        return [

            "show",

            "*",

            "from",

            $words[0]

        ];

    }

    return $tokens;

}