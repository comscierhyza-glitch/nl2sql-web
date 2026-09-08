<?php

function scoreConfidence(array $parsed): array
{
    /*
    |--------------------------------------------------------------------------
    | No semantic score
    |--------------------------------------------------------------------------
    */

    if (
        empty($parsed["semantic"]["table_score"])
    ) {
        return $parsed;
    }

    $score = $parsed["semantic"]["table_score"];

    /*
    |--------------------------------------------------------------------------
    | Confidence Levels
    |--------------------------------------------------------------------------
    */

    if ($score >= 15) {

        $confidence = "HIGH";

    } elseif ($score >= 8) {

        $confidence = "MEDIUM";

    } else {

        $confidence = "LOW";

    }

    $parsed["semantic"]["confidence"] = $confidence;

    return $parsed;
}