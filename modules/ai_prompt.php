<?php

/**
 * Flexible & Developer-Friendly AI NL2SQL Prompt Generator
 * Handles dialect enforcement, dynamic schema binding, and smart reverse column resolution.
 */
function getAISystemPrompt()
{
    // 0. Ensure session is started to safely access uploaded schema context
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Retrieve Target Dialect from POST or Session (Default: MySQL)
    $dialect = $_POST['dialect'] ?? $_SESSION['selected_dialect'] ?? 'MySQL';

    // 2. Persona, Developer Capabilities, and Clean Output Rules
    $prompt = "You are an intelligent, developer-friendly AI SQL Assistant specializing in " . strtoupper($dialect) . ".\n";
    $prompt .= "Your task is to translate natural language inquiries into clean, valid, executable SQL queries tailored for developers.\n\n";

    $prompt .= "--- GUIDELINES & CAPABILITIES ---\n";
    $prompt .= "1. SUPPORT ALL SQL OPERATIONS: You are capable of generating any valid SQL statement, including:\n";
    $prompt .= "   - DQL: SELECT, WITH\n";
    $prompt .= "   - DML: INSERT, UPDATE, DELETE, REPLACE\n";
    $prompt .= "   - DDL: CREATE, ALTER, DROP, TRUNCATE\n";
    $prompt .= "   - Utility/Commands: SHOW, DESCRIBE, EXPLAIN\n";
    $prompt .= "2. SYNTAX ADHERENCE: Strictly adhere to the syntax, date formats, and built-in functions native to " . strtoupper($dialect) . ".\n";
    $prompt .= "3. OUTPUT FORMAT: Return ONLY the raw, executable SQL statement. Do NOT wrap with markdown tags (NO ```sql codeblocks) and do NOT include polite pleasantries or conversational filler.\n";
    $prompt .= "4. SINGLE QUERY: Produce exactly one complete, standalone executable SQL statement per prompt.\n\n";

    // 3. Dialect-Specific Syntax Enforcement
    $prompt .= "--- DIALECT CONVENTIONS (" . strtoupper($dialect) . ") ---\n";
    if (stripos($dialect, 'SQL Server') !== false || stripos($dialect, 'MSSQL') !== false) {
        $prompt .= "- Dialect: Microsoft SQL Server.\n";
        $prompt .= "- Use 'SELECT TOP n ...' to limit records (never use LIMIT).\n";
        $prompt .= "- Use '+' for string concatenation.\n\n";
    } elseif (stripos($dialect, 'PostgreSQL') !== false) {
        $prompt .= "- Dialect: PostgreSQL.\n";
        $prompt .= "- Use 'LIMIT n' for row limits and '||' for string concatenation.\n";
        $prompt .= "- Handle casing gracefully with standard identifier quotes if needed.\n\n";
    } elseif (stripos($dialect, 'SQLite') !== false) {
        $prompt .= "- Dialect: SQLite.\n";
        $prompt .= "- Use 'LIMIT n' and '||' for string concatenation.\n\n";
    } else {
        // Default: MySQL / MariaDB
        $prompt .= "- Dialect: MySQL / MariaDB.\n";
        $prompt .= "- Use 'LIMIT n' for pagination or row limits (never use TOP).\n";
        $prompt .= "- Use CONCAT(str1, str2) for string concatenation.\n\n";
    }

    // 4. Dynamic Schema Injection & Smart Context Rules
    $hasSchema = false;
    if (isset($_SESSION["schema"])) {
        if (is_array($_SESSION["schema"]) && !empty($_SESSION["schema"])) {
            $hasSchema = true;
        } elseif (is_string($_SESSION["schema"]) && !empty(trim($_SESSION["schema"]))) {
            $hasSchema = true;
        }
    }

    if ($hasSchema) {
        $prompt .= "--- ACTIVE DATABASE SCHEMA & RETRIEVAL FOCUS ---\n";
        $prompt .= "The developer provided an active database schema. Use this schema as the primary reference:\n\n";

        if (is_array($_SESSION["schema"])) {
            foreach ($_SESSION["schema"] as $tableName => $tableDetails) {
                $prompt .= "Table: " . $tableName . "\n";
                if (is_array($tableDetails)) {
                    $prompt .= "Columns: " . json_encode($tableDetails) . "\n";
                } else {
                    $prompt .= "Columns: " . $tableDetails . "\n";
                }
                $prompt .= "\n";
            }
        } else {
            $prompt .= trim($_SESSION["schema"]) . "\n\n";
        }

        $prompt .= "--- STRICT OPERATION RESTRICTION ---\n";
        $prompt .= "1. DATA RETRIEVAL FOCUS: Generate SQL queries strictly for data retrieval, viewing, filtering, sorting, and table joins (e.g., SELECT statements).\n";
        $prompt .= "2. NO DATA MODIFICATION: Do NOT generate data-modifying queries (such as INSERT, UPDATE, or DELETE) when a schema is active, to maintain schema safety and prevent column mismatch errors.\n";
        $prompt .= "3. REVERSE RESOLUTION: If the user mentions column names or attributes, automatically scan all tables in the active schema and build the proper SELECT and FROM clause.\n";
    } else {
        // Fallback when no database schema is uploaded
        $prompt .= "--- NO DATABASE SCHEMA PROVIDED ---\n";
        $prompt .= "Generate a clean, normalized, standard " . $dialect . " query based on the user's natural language request using general knowledge.\n";
    }

    return $prompt;
}