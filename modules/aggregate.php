<?php

function detectAggregate($tokens, $table = null)
{
    global $aggregates;

    $lower = array_map("strtolower", $tokens);

    $havingIndex = array_search("having", $lower);

    foreach ($lower as $i => $word) {

        if ($havingIndex !== false && $i > $havingIndex) {
            continue;
        }

        if (isset($aggregates[$word])) {

            $function = strtoupper($aggregates[$word]);

            $column = "*";

            if (isset($lower[$i + 1])) {

                $next = $lower[$i + 1];

                /*
                |--------------------------------------------------------------------------
                | COUNT(table)  -> COUNT(*)
                |--------------------------------------------------------------------------
                */

                if (
                    $function == "COUNT" &&
                    $table &&
                    $next == strtolower($table)
                ) {

                    $column = "*";
                } else {

                    $column = $next;
                }
            }

            return [

                "keyword"  => $word,
                "function" => $function,
                "column"   => $column,
                "index"    => $i

            ];
        }
    }

    return null;
}

function detectAggregates($tokens, $table = null)
{
    global $aggregates;

    $lower = array_map("strtolower", $tokens);

    $havingIndex = array_search("having", $lower);

    $results = [];

    foreach ($lower as $i => $word) {

        /*
    |--------------------------------------------------------------------------
    | Ignore HAVING clause
    |--------------------------------------------------------------------------
    */

        if ($havingIndex !== false && $i > $havingIndex) {
            continue;
        }

        /*
    |--------------------------------------------------------------------------
    | Skip alias name
    | Example:
    | average salary AS avg
    |--------------------------------------------------------------------------
    */

        if (
            $i > 0 &&
            $lower[$i - 1] == "as"
        ) {
            continue;
        }

        if (!isset($aggregates[$word])) {
            continue;
        }

        $function = strtoupper($aggregates[$word]);

        $column = "*";

        if (isset($lower[$i + 1])) {

            $next = $lower[$i + 1];

            /*
    |--------------------------------------------------------------------------
    | Skip SQL keywords after aggregate
    |--------------------------------------------------------------------------
    */

            if (
                in_array(
                    $next,
                    [
                        "as",
                        "and",
                        "or",
                        "from",
                        "where",
                        "group",
                        "order",
                        "having",
                        "limit"
                    ]
                )
            ) {

                $column = "*";
            } elseif (
                $function == "COUNT" &&
                $table &&
                $next == strtolower($table)
            ) {

                $column = "*";
            } else {

                $column = $next;
            }
        }

        $results[] = [

            "keyword"  => $word,
            "function" => $function,
            "column"   => $column,
            "index"    => $i

        ];
    }

    return $results;
}
