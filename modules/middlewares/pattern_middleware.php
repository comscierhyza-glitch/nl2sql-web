<?php

require_once __DIR__ . "/../parser.php";

function handlePattern(array &$context): bool
{
    if (empty($context["tokens"])) {
        return false;
    }

    $context["pattern"] = applyPatterns(
        $context["tokens"]
    );

    return true;
}