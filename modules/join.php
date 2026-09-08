<?php
function detectJoin($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        $word = strtolower($tokens[$i]);

        // ==========================
        // NORMAL JOIN
        // ==========================

        if ($word == "join") {
            return [

                "type" => "JOIN",

                "table" => $tokens[$i + 1] ?? null,

                "on" => detectJoinOn($tokens)

            ];
        }

        // ==========================
        // INNER JOIN
        // ==========================

        if (
            $word == "inner" &&
            strtolower($tokens[$i + 1] ?? "") == "join"
        ) {
            return [

                "type" => "INNER JOIN",

                "table" => $tokens[$i + 2] ?? null,

                "on" => detectJoinOn($tokens)

            ];
        }

        // ==========================
        // LEFT JOIN
        // ==========================

        if (
            $word == "left" &&
            strtolower($tokens[$i + 1] ?? "") == "join"
        ) {
            return [

                "type" => "LEFT JOIN",

                "table" => $tokens[$i + 2] ?? null,

                "on" => detectJoinOn($tokens)

            ];
        }

        // ==========================
        // RIGHT JOIN
        // ==========================

        if (
            $word == "right" &&
            strtolower($tokens[$i + 1] ?? "") == "join"
        ) {
            return [

                "type" => "RIGHT JOIN",

                "table" => $tokens[$i + 2] ?? null,

                "on" => detectJoinOn($tokens)

            ];
        }
    }

    return null;
}