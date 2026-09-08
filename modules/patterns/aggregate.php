<?php

function applyAggregatePatterns(array $tokens): array
{
    global $aggregateWords;

    $words = array_map("strtolower", $tokens);

    /*
    |--------------------------------------------------------------------------
    | COUNT
    |--------------------------------------------------------------------------
    |
    | count students
    | number students
    | how many students
    |
    */

    if (count($words) == 2 && $words[0] == "count") {

        return [

            "count",

            $words[1]

        ];

    }

    if (count($words) == 2 && $words[0] == "number") {

        return [

            "count",

            $words[1]

        ];

    }

    if (

        count($words) == 3 &&

        $words[0] == "how" &&

        $words[1] == "many"

    ) {

        return [

            "count",

            $words[2]

        ];

    }

    /*
    |--------------------------------------------------------------------------
    | SUM
    |--------------------------------------------------------------------------
    |
    | total salary employee
    | sum salary employee
    | total salary from employee
    | sum salary from employee
    |
    */

    if (

        count($words) >= 2 &&

        in_array($words[0], ["total", "sum"])

    ) {

        return [

            "show",

            "sum",

            $words[1],

            "from",

            $words[count($words) - 1]

        ];

    }

    /*
    |--------------------------------------------------------------------------
    | AVG
    |--------------------------------------------------------------------------
    |
    | average salary employee
    | average salary from employee
    |
    */

    if (

        count($words) >= 2 &&

        $words[0] == "average"

    ) {

        return [

            "show",

            "average",

            $words[1],

            "from",

            $words[count($words) - 1]

        ];

    }

    /*
    |--------------------------------------------------------------------------
    | MAX
    |--------------------------------------------------------------------------
    |
    | highest salary employee
    | maximum salary employee
    | max salary employee
    |
    */

    if (

        count($words) >= 2 &&

        in_array($words[0], ["highest", "maximum", "max"])

    ) {

        return [

            "show",

            "max",

            $words[1],

            "from",

            $words[count($words) - 1]

        ];

    }

    /*
    |--------------------------------------------------------------------------
    | MIN
    |--------------------------------------------------------------------------
    |
    | lowest salary employee
    | minimum salary employee
    | min salary employee
    |
    */

    if (

        count($words) >= 2 &&

        in_array($words[0], ["lowest", "minimum", "min"])

    ) {

        return [

            "show",

            "min",

            $words[1],

            "from",

            $words[count($words) - 1]

        ];

    }

    return $tokens;
}