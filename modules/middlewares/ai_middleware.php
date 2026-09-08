<?php

require_once __DIR__ . "/../ai_standardizer.php";

function handleAI(array &$context): bool
{
    $input = trim($context["input"] ?? "");

    if (empty($input)) {
        return false;
    }

    // Tawagon ang Triple-Provider Engine sa ai_standardizer.php
    $sql = standardizeInput($input);

    // 1. Kung naay valid SQL ug walay ERROR
    if (!empty($sql) && strpos($sql, 'ERROR:') === false) {
        $context["sql"] = $sql;
        $context["ai"]["used"] = true;
        return true;
    }

    // 2. Kung naay specific error gikan sa standardizer, ipakita ang tinuod nga error
    if (!empty($sql) && strpos($sql, 'ERROR:') !== false) {
        $context["sql"] = $sql;
    } else {
        // Fallback kung blangko gyud ang gi-return
        $context["sql"] = "-- ERROR: AI returned an empty response. Please check your API Keys or Network.";
    }

    $context["ai"]["used"] = false;
    return false;
}