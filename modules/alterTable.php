<?php
function detectAlterTable($tokens)
{
    $result = [

        "action" => null,
        "column" => null,
        "type" => null

    ];

    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        $word = strtolower($tokens[$i]);

        // ADD COLUMN
        if ($word == "add") {
            $result["action"] = "ADD COLUMN";
            $result["column"] = $tokens[$i + 2] ?? null;
            $result["type"] = strtoupper($tokens[$i + 3] ?? "");

            return $result;
        }

        // DROP COLUMN
        if ($word == "drop") {
            $result["action"] = "DROP COLUMN";
            $result["column"] = $tokens[$i + 2] ?? null;

            return $result;
        }

        // MODIFY COLUMN
        if ($word == "modify") {
            $result["action"] = "MODIFY COLUMN";
            $result["column"] = $tokens[$i + 2] ?? null;
            $result["type"] = strtoupper($tokens[$i + 3] ?? "");

            return $result;
        }
    }

    return null;
}