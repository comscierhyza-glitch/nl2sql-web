<?php

$groupMap = include __DIR__ . "/../../rules/groupby_map.php";

function applyHavingPatterns(array $tokens): array
{
    global $groupMap;

    $words = array_map("strtolower", $tokens);

    /*
    ------------------------------------------------------
    departments having more than 5 employees
    ------------------------------------------------------
    */

    if (
        count($words) >= 6 &&
        $words[1] == "having" &&
        in_array($words[2], ["more", "greater"]) &&
        $words[3] == "than"
    ) {

        $groupColumn = $groupMap[$words[0]] ?? $words[0];

        return [

            "show",

            "count",

            "*",

            "from",

            $words[5],

            "group",

            "by",

            $groupColumn,

            "having",

            "count",

            ">",

            $words[4]

        ];
    }

    /*
    ------------------------------------------------------
    departments having less than 5 employees
    ------------------------------------------------------
    */

    if (
        count($words) >= 6 &&
        $words[1] == "having" &&
        $words[2] == "less" &&
        $words[3] == "than"
    ) {

        $groupColumn = $groupMap[$words[0]] ?? $words[0];

        return [

            "show",

            "count",

            "*",

            "from",

            $words[5],

            "group",

            "by",

            $groupColumn,

            "having",

            "count",

            "<",

            $words[4]

        ];
    }

    return $tokens;
}