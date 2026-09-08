<?php

function applyTruncatePatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    // empty student table
    if (
        count($words) == 3 &&
        $words[0] == "empty" &&
        $words[2] == "table"
    ) {

        return [

            "truncate",

            "table",

            $words[1]

        ];

    }

    // clear student table
    if (
        count($words) == 3 &&
        $words[0] == "clear" &&
        $words[2] == "table"
    ) {

        return [

            "truncate",

            "table",

            $words[1]

        ];

    }

    // clear all records from student
    if (
        count($words) >= 5 &&
        $words[0] == "clear" &&
        $words[1] == "all" &&
        $words[2] == "records" &&
        $words[3] == "from"
    ) {

        return [

            "truncate",

            "table",

            end($words)

        ];

    }

    // remove all rows from student
    if (
        count($words) >= 5 &&
        $words[0] == "remove" &&
        $words[1] == "all" &&
        $words[2] == "rows" &&
        $words[3] == "from"
    ) {

        return [

            "truncate",

            "table",

            end($words)

        ];

    }

    return $tokens;

}