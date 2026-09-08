<?php

function detectRelationship($tokens)
{
    global $relationships;

    $words = array_map("strtolower", $tokens);

    $count = count($words);

    for ($i = 0; $i < $count - 2; $i++) {

        $table1 = $words[$i];

        $connector = $words[$i + 1];

        $table2 = $words[$i + 2];

        if (
            !in_array($connector, ["with", "and"])
        ) {
            continue;
        }

        if (
            isset($relationships[$table1][$table2])
        ) {

            $relation = $relationships[$table1][$table2];

            return [

                "type" => $relation["type"],

                "table" => $table2,

                "on" =>

                    $table1 . "." . $relation["left"] .

                    "=" .

                    $table2 . "." . $relation["right"]

            ];
        }
    }

    return null;
}