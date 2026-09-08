<?php
function detectCreateTableColumns($tokens)
{
    $columns = [];

    $count = count($tokens);

    for ($i = 3; $i < $count; $i += 2) {
        if (isset($tokens[$i], $tokens[$i + 1])) {
            $columns[] = [

                "column" => $tokens[$i],
                "type" => strtoupper($tokens[$i + 1])

            ];
        }
    }

    return $columns;
}