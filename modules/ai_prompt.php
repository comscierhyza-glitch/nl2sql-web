<?php



function getAISystemPrompt()

{

    if (session_status() === PHP_SESSION_NONE) {

        session_start();

    }



    $dialect = $_POST['dialect'] ?? $_SESSION['selected_dialect'] ?? 'MySQL';



    $prompt = "You are an expert SQL assistant specializing in " . strtoupper($dialect) . ".\n";

    $prompt .= "Translate the natural language input into a clean, valid, and executable SQL query. Return ONLY the raw SQL without markdown tags or explanations.\n\n";

   

    // Instruksyon para sa sakto nga pag-handle sa IDs ug Foreign Keys

    $prompt .= "CRITICAL COLUMN & FILTER RULES:\n";

    $prompt .= "1. Pay close attention to foreign keys (like user_id, bus_no, etc.). If the user asks for records associated with a specific user ID or foreign reference, filter using that foreign key column (e.g., WHERE user_id = 3) instead of filtering by the table's primary key 'id'.\n";

    $prompt .= "2. When a user asks to 'list all expenses', 'show all records', or requests descriptions/details, use SELECT * or select all relevant descriptive columns rather than just returning an ID.\n\n";



    if (isset($_SESSION["schema"]) && !empty($_SESSION["schema"])) {

        $prompt .= "DATABASE SCHEMA:\n";

        $prompt .= (is_array($_SESSION["schema"]) ? print_r($_SESSION["schema"], true) : $_SESSION["schema"]) . "\n\n";

    }



    return $prompt;

}