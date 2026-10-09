<?php

function getAISystemPrompt()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $dialect = $_POST['dialect'] ?? $_SESSION['selected_dialect'] ?? 'MySQL';

    $prompt = "You are an expert SQL assistant specializing in " . strtoupper($dialect) . ".\n";
    $prompt .= "Translate the natural language input into a clean, valid, and executable SQL query. Return ONLY the raw SQL without markdown tags or explanations.\n\n";
    
    $prompt .= "CRITICAL COLUMN & SELECT RULES:\n";
    $prompt .= "1. EXACT COLUMN MATCHING: If the user explicitly mentions a specific column name or attribute (e.g., 'brand_description'), you MUST SELECT that exact column (e.g., SELECT brand_description FROM brand_tbl). Never replace it with 'id' or 'brand_id' when a specific field is requested.\n";
    $prompt .= "2. FOREIGN KEYS: If the user asks for records associated with a specific reference ID, filter using that foreign key column (e.g., WHERE user_id = 3) instead of the primary key 'id'.\n";
    $prompt .= "3. GENERAL LISTINGS: When a user asks to 'list all', 'show all records', or requests general details without specific column names, use SELECT *\n\n";

    if (isset($_SESSION["schema"]) && !empty($_SESSION["schema"])) {
        $prompt .= "DATABASE SCHEMA:\n";
        $prompt .= (is_array($_SESSION["schema"]) ? print_r($_SESSION["schema"], true) : $_SESSION["schema"]) . "\n\n";
    }

    return $prompt;
}