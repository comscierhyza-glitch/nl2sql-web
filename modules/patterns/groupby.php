<?php

function applyGroupByPatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    /*
|--------------------------------------------------------------------------
| group employees by department
|--------------------------------------------------------------------------
*/

    if (
        count($words) >= 4 &&
        $words[0] == "group" &&
        $words[2] == "by"
    ) {

        return [

            "show",
            "*",
            "from",
            $words[1],

            "group",
            "by",

            $words[3]

        ];
    }

    /*
    --------------------------------------------------------
    employees grouped by department
    students grouped by course
    --------------------------------------------------------
    */

    if (
        count($words) >= 4 &&
        $words[1] == "grouped" &&
        $words[2] == "by"
    ) {

        return [

            "show",
            "*",
            "from",
            $words[0],
            "group",
            "by",
            $words[3]

        ];
    }

    /*
    --------------------------------------------------------
    employees group by department
    students group by course
    --------------------------------------------------------
    */

    if (
        count($words) >= 4 &&
        $words[1] == "group" &&
        $words[2] == "by"
    ) {

        return [

            "show",
            "*",
            "from",
            $words[0],
            "group",
            "by",
            $words[3]

        ];
    }

    /*
    --------------------------------------------------------
    show employees grouped by department
    --------------------------------------------------------
    */

    if (
        count($words) >= 5 &&
        $words[0] == "show" &&
        $words[2] == "grouped" &&
        $words[3] == "by"
    ) {

        return [

            "show",
            $words[1],
            "group",
            "by",
            $words[4]

        ];
    }

    /*
    --------------------------------------------------------
    show employees group by department
    --------------------------------------------------------
    */

    if (
        count($words) >= 5 &&
        $words[0] == "show" &&
        $words[2] == "group" &&
        $words[3] == "by"
    ) {

        return [

            "show",
            $words[1],
            "group",
            "by",
            $words[4]

        ];
    }

    return $tokens;
}
