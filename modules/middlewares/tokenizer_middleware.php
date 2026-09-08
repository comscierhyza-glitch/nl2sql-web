<?php

require_once __DIR__ . "/../tokenizer.php";

function handleTokenizer(array &$context): bool
{
    /*
    |--------------------------------------------------------------------------
    | Check if standardized input exists
    |--------------------------------------------------------------------------
    */

    if (empty($context["standardized"])) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Tokenize
    |--------------------------------------------------------------------------
    */

    $context["tokens"] = tokenize(
        $context["standardized"]
    );

    return true;
}