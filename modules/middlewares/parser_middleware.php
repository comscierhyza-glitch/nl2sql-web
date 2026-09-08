<?php

require_once __DIR__ . "/../parser.php";

function handleParser(array &$context): bool
{
    /*
    |--------------------------------------------------------------------------
    | Check Pattern Tokens
    |--------------------------------------------------------------------------
    */

    if (empty($context["pattern"])) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Parse Pattern Tokens
    |--------------------------------------------------------------------------
    */

    $context["parsed"] = parseInput(
        $context["pattern"]
    );

    return true;
}