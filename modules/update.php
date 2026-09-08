<?php
function detectUpdate($tokens)
{
    $update = [

        "columns" => [],
        "values" => []

    ];

    $count = count($tokens);

    for ($i = 2; $i < $count; $i += 2) {
        if (strtolower($tokens[$i]) == "where") {
            break;
        }

        if (isset($tokens[$i + 1])) {
            $update["columns"][] = strtolower($tokens[$i]);

            $update["values"][] = $tokens[$i + 1];
        }
    }

    return $update;
}