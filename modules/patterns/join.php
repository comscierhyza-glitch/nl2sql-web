<?php

function applyJoinPatterns(array $tokens): array
{
    global $relationships;

    $words = array_map("strtolower", $tokens);

    /*
    |--------------------------------------------------------------------------
    | SMART NATURAL LANGUAGE JOIN
    |--------------------------------------------------------------------------
    |
    | students with courses
    | employees with departments
    | students and grades
    |
    */

    if (count($words) == 3) {

        $table1 = $words[0];

        $connector = $words[1];

        $table2 = $words[2];

        if (

            in_array($connector, ["with", "and"]) &&

            isset($relationships[$table1][$table2])

        ) {

            return [

                "show",

                "*",

                "from",

                $table1,

                "inner",

                "join",

                $table2

            ];

        }

    }

    return $tokens;
}