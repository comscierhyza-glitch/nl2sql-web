<?php

function validateCommand(array &$context): void
{
    // 1. Sulayi pagkuha ang command gikan sa daan nga parser setup
    $command = strtoupper($context["parsed"]["command"] ?? "");

    // 2. AI INTEGRATION: Kung empty, kuhaon nato ang first word gikan sa AI output
    if (empty($command)) {
        // Pangitaon ang AI output (kasagaran naka-save ni sa 'standardized' o 'input' key human sa AI stage)
        $ai_query = $context["standardized"] ?? $context["input"] ?? $context["query"] ?? ""; 
        
        // Gamiton ang strtok para makuha ang pinaka-unang word sa SQL query (ex. "SELECT")
        $first_word = strtok(trim($ai_query), " \n\t"); 
        $command = strtoupper($first_word);
        
        // I-save balik sa context para magamit sa sunod nga mga modules
        $context["parsed"]["command"] = $command; 
    }

    if (empty($command)) {
        $context["validation"]["status"] = "INVALID";
        $context["validation"]["errors"][] = "Unable to determine SQL command from AI output.";
        return;
    }

    // 3. I-update ang supported list (First word lang atong basahon para mas flexible)
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
        // Gi-apil nato ang $command sa error message para makita nimo unsay gi-block sa guard
        $context["validation"]["errors"][] = "Unsupported or unsafe SQL command detected: " . $command;
    }
}