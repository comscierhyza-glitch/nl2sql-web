<?php

function detectWhere($tokens)
{
    $count = count($tokens);

    /*
    |--------------------------------------------------------------------------
    | Find WHERE keyword
    |--------------------------------------------------------------------------
    */

    $start = -1;

    for ($i = 0; $i < $count; $i++) {

        if (strtolower($tokens[$i]) == "where") {
            $start = $i + 1;
            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Smart WHERE
    | Example:
    | show employees department = IT
    |--------------------------------------------------------------------------
    */

    if ($start == -1 && $count >= 5) {
        $start = 2;
    }

    if ($start == -1) {
        return null;
    }

    $conditions = [];

    $logic = null;

    $i = $start;

    while ($i < $count) {

        $word = strtolower($tokens[$i]);

        /*
        |--------------------------------------------------------------------------
        | Stop at other clauses
        |--------------------------------------------------------------------------
        */

        if (
            in_array($word, [
                "group",
                "having",
                "order",
                "limit"
            ])
        ) {
            break;
        }

        /*
        |--------------------------------------------------------------------------
        | Logical Operators
        |--------------------------------------------------------------------------
        */

        if ($word == "and") {
            $logic = "AND";
            $i++;
            continue;
        }

        if ($word == "or") {
            $logic = "OR";
            $i++;
            continue;
        }

        $column = $tokens[$i] ?? null;
        $column = detectColumnPhrase($tokens, $i);

        if (!$column) {
            break;
        }

        if (!$column) {
            break;
        }

        /*
        |--------------------------------------------------------------------------
        | BETWEEN
        |--------------------------------------------------------------------------
        */

        if (
            isset($tokens[$i + 1]) &&
            strtolower($tokens[$i + 1]) == "between"
        ) {

            $from = $tokens[$i + 2] ?? null;
            $to   = $tokens[$i + 4] ?? null;

            $condition = [

                "column"   => $column,
                "operator" => "BETWEEN",
                "from"     => trim($from, "'\""),
                "to"       => trim($to, "'\"")

            ];

            if ($logic != null) {
                $condition["logic"] = $logic;
            }

            $conditions[] = $condition;

            $logic = null;

            $i += 5;

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Detect Operator
        |--------------------------------------------------------------------------
        */

        $match = detectOperator($tokens, $i + 1);

        if (!$match) {
            break;
        }

        /*
        |--------------------------------------------------------------------------
        | IS NULL / IS NOT NULL
        |--------------------------------------------------------------------------
        */

        if (
            in_array($match["operator"], [
                "IS NULL",
                "IS NOT NULL"
            ])
        ) {

            $condition = [

                "column"   => $column,
                "operator" => $match["operator"]

            ];

            if ($logic != null) {
                $condition["logic"] = $logic;
            }

            $conditions[] = $condition;

            $logic = null;

            $i += $match["offset"] + $match["length"] + 1;

            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | IN / NOT IN
        |--------------------------------------------------------------------------
        */

        if (
            in_array($match["operator"], [
                "IN",
                "NOT IN"
            ])
        ) {

            $values = [];

            $startValues =
                $i + 1 +
                $match["offset"] +
                $match["length"];

            for ($j = $startValues; $j < $count; $j++) {

                $current = strtolower($tokens[$j]);

                if (
                    in_array($current, [
                        "and",
                        "or",
                        "group",
                        "having",
                        "order",
                        "limit"
                    ])
                ) {
                    break;
                }

                if ($current == ",") {
                    continue;
                }

                $values[] = trim($tokens[$j], "'\"");
            }

            $condition = [

                "column"   => $column,
                "operator" => $match["operator"],
                "value"    => $values

            ];

            if ($logic != null) {
                $condition["logic"] = $logic;
            }

            $conditions[] = $condition;

            $logic = null;

            $i = $j;

            continue;
        }

        /*
|--------------------------------------------------------------------------
| Normal Condition
|--------------------------------------------------------------------------
*/

        $valueIndex =
            $i + 1 +
            $match["offset"] +
            $match["length"];

        if (!isset($tokens[$valueIndex])) {
            break;
        }

        /*
|--------------------------------------------------------------------------
| Collect multi-word value
|--------------------------------------------------------------------------
*/

        $valueTokens = [];

        $j = $valueIndex;

        while ($j < $count) {

            $current = strtolower($tokens[$j]);

            // Stop kung naa nay logical operator o lain nga SQL clause
            if (in_array($current, [
                "and",
                "or",
                "group",
                "having",
                "order",
                "limit"
            ])) {
                break;
            }

            $valueTokens[] = trim($tokens[$j], "'\"");

            $j++;
        }

        $value = implode(" ", $valueTokens);

        $condition = [

            "column"   => $column,
            "operator" => $match["operator"],
            "value"    => $value

        ];

        if ($logic != null) {
            $condition["logic"] = $logic;
        }

        $conditions[] = $condition;

        $logic = null;

        $i = $j;
    }

    if (empty($conditions)) {
        return null;
    }

    return [

        "conditions" => $conditions

    ];
}
