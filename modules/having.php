<?php

function detectHaving($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {

        if (strtolower($tokens[$i]) == "having") {

            $aggregate = strtoupper($tokens[$i + 1] ?? "");

            /*
            |--------------------------------------------------------------------------
            | COUNT (no column required)
            |--------------------------------------------------------------------------
            */

            if ($aggregate == "COUNT") {

                $match = detectOperator($tokens, $i + 2);

                if (!$match) {
                    return null;
                }

                $value = $tokens[
                    $i + 2 + $match["offset"] + $match["length"]
                ] ?? null;

                if (!$value) {
                    return null;
                }

                return [

                    "aggregate" => "COUNT",

                    "column" => "*",

                    "operator" => $match["operator"],

                    "value" => trim($value, "'\"")

                ];
            }

            /*
            |--------------------------------------------------------------------------
            | AVG / SUM / MIN / MAX
            |--------------------------------------------------------------------------
            */

            $validAggregates = [

                "AVG",
                "AVERAGE",
                "SUM",
                "MIN",
                "MAX"

            ];

            if (in_array($aggregate, $validAggregates)) {

                if ($aggregate == "AVERAGE") {
                    $aggregate = "AVG";
                }

                $column = $tokens[$i + 2] ?? null;

                $match = detectOperator($tokens, $i + 3);

                if (!$match) {
                    return null;
                }

                $value = $tokens[
                    $i + 3 + $match["offset"] + $match["length"]
                ] ?? null;

                if (!$column || !$value) {
                    return null;
                }

                return [

                    "aggregate" => $aggregate,

                    "column" => $column,

                    "operator" => $match["operator"],

                    "value" => trim($value, "'\"")

                ];
            }

        }

    }

    return null;
}