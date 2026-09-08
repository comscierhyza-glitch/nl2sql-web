<?php

function detectInsertValues($tokens)
{
    $result = [

        "columns" => [],
        "values" => []

    ];

    $skip = [

        "add",
        "insert",
        "into",
        "save",
        "record"

    ];

    $count = count($tokens);

    for ($i = 2; $i < $count; $i++) {

        $column = strtolower($tokens[$i]);

        if (in_array($column, $skip)) {
            continue;
        }

        // Pattern: name = John
        if (
            isset($tokens[$i + 1]) &&
            $tokens[$i + 1] == "=" &&
            isset($tokens[$i + 2])
        ) {

            $result["columns"][] = $column;
            $result["values"][]  = $tokens[$i + 2];

            $i += 2;
            continue;
        }

        // Pattern: name John
        if (isset($tokens[$i + 1])) {

            $result["columns"][] = $column;
            $result["values"][]  = $tokens[$i + 1];

            $i++;
        }
    }

    return $result;
}