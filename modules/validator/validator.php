<?php

require_once __DIR__ . "/mode_detector.php";
require_once __DIR__ . "/command_validator.php";
require_once __DIR__ . "/syntax_validator.php";
require_once __DIR__ . "/table_validator.php";
require_once __DIR__ . "/column_validator.php";
require_once __DIR__ . "/join_validator.php";
require_once __DIR__ . "/autocorrect.php";
require_once __DIR__ . "/warning_generator.php";
require_once __DIR__ . "/validation_report.php";
require_once __DIR__ . "/../complexity_estimator.php";

function validateQuery(array &$context): bool
{

    /*
    |--------------------------------------------------------------------------
    | Default Validation Object
    |--------------------------------------------------------------------------
    */

    $context["validation"] = [

        "status" => "VALID",

        "mode" => "PARSER",

        "errors" => [],

        "warnings" => [],

        "autocorrections" => [],

        "confidence" => 100

    ];

    detectValidationMode($context);

    validateCommand($context);

    validateSyntax($context);

    validateTable($context);

    validateColumns($context);

    validateJoins($context);

    autoCorrect($context);

    generateWarnings($context);

    finalizeValidation($context);

    estimateQueryComplexity($context);

    return $context["validation"]["status"] != "INVALID";
}
