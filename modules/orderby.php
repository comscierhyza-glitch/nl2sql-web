<?php
function detectOrderBy($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {

        $word = strtolower($tokens[$i]);

        if ($word == "order" || $word == "sort") {

            $next = strtolower($tokens[$i + 1] ?? "");

            /*
            ----------------------------
            order by age
            sort by age
            ----------------------------
            */

            if ($next == "by") {

                $column = $tokens[$i + 2] ?? null;

                $direction = strtoupper($tokens[$i + 3] ?? "ASC");

            }

            /*
            ----------------------------
            order age
            sort age
            ----------------------------
            */

            else {

                $column = $tokens[$i + 1] ?? null;

                $direction = strtoupper($tokens[$i + 2] ?? "ASC");

            }

            /*
            ----------------------------
            Smart Direction
            ----------------------------
            */

            switch (strtolower($direction)) {

                case "descending":
                case "desc":
                    $direction = "DESC";
                    break;

                case "ascending":
                case "asc":
                    $direction = "ASC";
                    break;

                default:
                    $direction = "ASC";
            }

            return [

                "column" => $column,

                "direction" => $direction

            ];
        }
    }

    return null;
}