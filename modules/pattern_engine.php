<?php

require_once __DIR__ . "/patterns/select.php";
require_once __DIR__ . "/patterns/distinct.php";
require_once __DIR__ . "/patterns/truncate.php";
require_once __DIR__ . "/patterns/order.php";
require_once __DIR__ . "/patterns/limit.php";
require_once __DIR__ . "/patterns/aggregate.php";
require_once __DIR__ . "/patterns/join.php";
require_once __DIR__ . "/patterns/whereComparison.php";
require_once __DIR__ . "/patterns/whereAge.php";
require_once __DIR__ . "/patterns/whereContext.php";
require_once __DIR__ . "/patterns/between.php";
require_once __DIR__ . "/patterns/groupby.php";
require_once __DIR__ . "/patterns/having.php";
require_once __DIR__ . "/patterns/like.php";

/*
|--------------------------------------------------------------------------
| Natural Language Operator Dictionary
|--------------------------------------------------------------------------
*/

$comparisonWords = [

    "greater" => ">",
    "above"   => ">",
    "more"    => ">",

    "less"    => "<",
    "below"   => "<",
    "under"   => "<",

    "equal"   => "=",
    "equals"  => "=",

    "older"   => ">",
    "younger" => "<"

];

/*
|--------------------------------------------------------------------------
| Sorting Dictionary
|--------------------------------------------------------------------------
*/

$sortingWords = [

    "sorted",
    "sort",
    "ordered",
    "order"

];

$directionWords = [

    "ascending"  => "asc",
    "asc"        => "asc",

    "descending" => "desc",
    "desc"       => "desc"

];

/*
|--------------------------------------------------------------------------
| Human Attribute Dictionary
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Context-Aware Attribute Dictionary
|--------------------------------------------------------------------------
*/

$contextInference = [

    "students" => [

        "named"  => "name",
        "called" => "name",

        "older"   => "age",
        "younger" => "age",

        "from" => "address",

        "in" => "course"

    ],

    "employees" => [

        "named"  => "name",
        "called" => "name",

        "older"   => "age",
        "younger" => "age",

        "from" => "department"

    ],

    "teachers" => [

        "named"  => "name",

        "older"   => "age",
        "younger" => "age",

        "from" => "school"

    ]

];

/*
|--------------------------------------------------------------------------
| LIMIT WORDS
|--------------------------------------------------------------------------
*/

$limitWords = [

    "top",

    "first"

];


/*
|--------------------------------------------------------------------------
| Rule-Based Pattern Recognition Engine
|--------------------------------------------------------------------------
*/

function applyPatterns(array $tokens): array
{
    global $comparisonWords;

    $words = array_map('strtolower', $tokens);

    $tokens = applyTruncatePatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applySelectPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyDistinctPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyWhereComparisonPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyWhereAgePatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyWhereContextPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyLikePatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyOrderPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyLimitPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyAggregatePatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyGroupByPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyHavingPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyBetweenPatterns($tokens);

    $words = array_map("strtolower", $tokens);

    $tokens = applyJoinPatterns($tokens);

    $words = array_map("strtolower", $tokens);


    /*
    |--------------------------------------------------------------------------
    | NO PATTERN FOUND
    |--------------------------------------------------------------------------
    */

    return $tokens;
}
