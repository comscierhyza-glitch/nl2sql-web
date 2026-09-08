<?php

function resolveAmbiguity(array $parsed): array
{
    /*
    |--------------------------------------------------------------------------
    | No semantic candidates
    |--------------------------------------------------------------------------
    */

    if (
        empty($parsed["semantic"]["table_candidates"])
    ) {
        return $parsed;
    }

    $candidates = $parsed["semantic"]["table_candidates"];

    arsort($candidates);

    $scores = array_values($candidates);

    /*
    |--------------------------------------------------------------------------
    | Only one candidate
    |--------------------------------------------------------------------------
    */

    if (count($scores) <= 1) {

        $parsed["semantic"]["ambiguous"] = false;

        return $parsed;
    }

    $highest = $scores[0];
    $second  = $scores[1];

    /*
    |--------------------------------------------------------------------------
    | Same score = ambiguous
    |--------------------------------------------------------------------------
    */

    if ($highest == $second && $highest > 0) {

        $parsed["semantic"]["ambiguous"] = true;

        $parsed["semantic"]["message"] =
            "Multiple possible tables detected.";

    } else {

        $parsed["semantic"]["ambiguous"] = false;

    }

    return $parsed;
}