<?php

function applyLikePatterns(array $tokens): array
{
    $words = array_map("strtolower", $tokens);

    /*
    ----------------------------------------------------
    starts with
    ----------------------------------------------------
    */

    for ($i = 0; $i < count($words) - 2; $i++) {

        if (
            $words[$i] == "starts" &&
            $words[$i + 1] == "with"
        ) {

            $tokens[$i] = "like";
            $tokens[$i + 1] = $tokens[$i + 2] . "%";

            array_splice($tokens, $i + 2, 1);

            return $tokens;
        }
    }

    /*
    ----------------------------------------------------
    ends with
    ----------------------------------------------------
    */

    for ($i = 0; $i < count($words) - 2; $i++) {

        if (
            $words[$i] == "ends" &&
            $words[$i + 1] == "with"
        ) {

            $tokens[$i] = "like";
            $tokens[$i + 1] = "%" . $tokens[$i + 2];

            array_splice($tokens, $i + 2, 1);

            return $tokens;
        }
    }

    /*
    ----------------------------------------------------
    contains
    ----------------------------------------------------
    */

    for ($i = 0; $i < count($words) - 1; $i++) {

        if ($words[$i] == "contains") {

            $tokens[$i] = "like";
            $tokens[$i + 1] = "%" . $tokens[$i + 1] . "%";

            return $tokens;
        }
    }

    /*
----------------------------------------------------
not contains
----------------------------------------------------
*/

    for ($i = 0; $i < count($words) - 2; $i++) {

        if (
            $words[$i] == "not" &&
            $words[$i + 1] == "contains"
        ) {

            $tokens[$i] = "not";
            $tokens[$i + 1] = "like";
            $tokens[$i + 2] = "%" . $tokens[$i + 2] . "%";

            return $tokens;
        }
    }

    return $tokens;
}
