<?php

require_once __DIR__ . "/../keyword_extractor.php";

function handleKeywords(array &$context): bool
{
    if (empty($context["parsed"])) {
        return false;
    }

    $context["keywords"] = extractDetectedKeywords(
        $context["parsed"]
    );

    return true;
}