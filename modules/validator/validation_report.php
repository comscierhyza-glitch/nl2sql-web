<?php

function finalizeValidation(array &$context): void
{
    $validation = &$context["validation"];

    /*
    |--------------------------------------------------------------------------
    | Start Confidence
    |--------------------------------------------------------------------------
    */

    $confidence = 100;

    /*
    |--------------------------------------------------------------------------
    | Deduct for Warnings
    |--------------------------------------------------------------------------
    */

    $confidence -= count($validation["warnings"]) * 5;

    /*
    |--------------------------------------------------------------------------
    | Deduct for Auto Corrections
    |--------------------------------------------------------------------------
    */

    $confidence -= count($validation["autocorrections"]) * 3;

    /*
    |--------------------------------------------------------------------------
    | Deduct for Semantic Inference
    |--------------------------------------------------------------------------
    */

    if (!empty($context["semantic"]["tables"]["resolved"])) {

        if (empty($context["parsed"]["table"])) {

            $confidence -= 2;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Deduct for Unresolved Semantic Columns
    |--------------------------------------------------------------------------
    */

    if (!empty($context["semantic"]["columns"])) {

        foreach ($context["semantic"]["columns"] as $column) {

            if (

                ($column["matched_by"] ?? "") == "unresolved"

            ) {

                $confidence -= 10;

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Minimum Confidence
    |--------------------------------------------------------------------------
    */

    if ($confidence < 0) {

        $confidence = 0;

    }

    $validation["confidence"] = $confidence;

    /*
    |--------------------------------------------------------------------------
    | Final Status
    |--------------------------------------------------------------------------
    */

    if (!empty($validation["errors"])) {

        $validation["status"] = "INVALID";

        $validation["confidence"] = 0;

        return;

    }

    if (!empty($validation["autocorrections"])) {

        $validation["status"] = "AUTO_CORRECTED";

        return;

    }

    if (!empty($validation["warnings"])) {

        $validation["status"] = "VALID_WITH_WARNINGS";

        return;

    }

    $validation["status"] = "VALID";
}