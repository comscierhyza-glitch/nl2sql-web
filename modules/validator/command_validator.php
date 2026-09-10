<?php

function validateCommand(array &$context): void
{
    // 1. Retrieve command from parser context if available
    $command = strtoupper($context["parsed"]["command"] ?? "");

    // 2. Fallback: Extract the first word directly from generated SQL or input
    if (empty($command)) {
        // Prioritize actual generated SQL before fallbacks
        $ai_query = $context["sql"] ?? $context["standardized"] ?? $context["input"] ?? $context["query"] ?? ""; 
        
        // Extract the leading keyword (e.g., "SELECT")
        $first_word = strtok(trim($ai_query), " \n\t"); 
        $command = strtoupper($first_word);
        
        // Update context with parsed command
        $context["parsed"]["command"] = $command; 
    }

    if (empty($command)) {
        $context["validation"]["status"] = "INVALID";
        $context["validation"]["errors"][] = "Unable to determine SQL command from AI output.";
        return;
    }

    // 3. Validate against supported DDL/DML commands
    $supported = [
        "SELECT",
        "INSERT",
        "UPDATE",
        "DELETE",
        "CREATE",
        "DROP",
        "USE",
        "ALTER",
        "TRUNCATE"
    ];

    if (!in_array($command, $supported)) {
        $context["validation"]["status"] = "INVALID";
        $context["validation"]["errors"][] = "Unsupported or unsafe SQL command detected: " . $command;
    }
}