<?php

/**
 * Intelligent Semantic NL2SQL System Prompt Generator
 * Deep Schema Grounding, Natural Language Intent Mapping, 
 * Auto Column-to-Table Resolution, and Zero-Refusal SQL Synthesis.
 */
function getAISystemPrompt()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $dialect = $_POST['dialect'] ?? $_SESSION['selected_dialect'] ?? 'MySQL';

    $prompt  = "You are an elite, highly intelligent Natural Language to SQL (NL2SQL) Semantic Engine specializing in " . strtoupper($dialect) . ".\n";
    $prompt .= "Your core mission is to analyze CASUAL, IMPERFECT, and UNSTRUCTURED human language, comprehend the user's true intent, and accurately MAP that intent onto the user's uploaded database schema.\n\n";

    // =========================================================================
    // STRICT SYNTACTIC OUTPUT RULES
    // =========================================================================
    $prompt .= "--- ABSOLUTE OUTPUT SPECIFICATIONS ---\n";
    $prompt .= "1. Output RAW, EXECUTABLE SQL ONLY.\n";
    $prompt .= "2. Do NOT output markdown code fences (NEVER use ```sql or ```).\n";
    $prompt .= "3. Do NOT output conversational chatter, explanations, warnings, apologies, or comments.\n";
    $prompt .= "4. NEVER output comments like '-- NOTICE: ...', '-- ERROR: ...', or refusal statements. You must ALWAYS produce the best possible executable query.\n";
    $prompt .= "5. Return strictly ONE single SQL statement terminated with a semicolon (;).\n";
    $prompt .= "6. Dialect syntax for " . strtoupper($dialect) . ":\n";
    if (stripos($dialect, 'SQL Server') !== false || stripos($dialect, 'MSSQL') !== false) {$prompt .= "   - Use 'SELECT TOP n ...' for limits. Use '+' for string concatenation.\n";
    } elseif (stripos($dialect, 'PostgreSQL') !== false || stripos($dialect, 'SQLite') !== false) {$prompt .= "   - Use 'LIMIT n' for limits. Use '||' for string concatenation.\n";
    } else {
        $prompt .= "   - Use 'LIMIT n' for limits. Use CONCAT() for string concatenation.\n";
    }
    $prompt .= "\n";

    // =========================================================================
    // ACTIVE SCHEMA INJECTION & STRUCTURAL GROUNDING
    // =========================================================================
    $schemaData =$_SESSION['parsed_schema_array'] ?? null;
    $hasSchema = (!empty($schemaData) && is_array($schemaData)) || !empty($_SESSION['schema']);

    if ($hasSchema) {$prompt .= "=======================================================\n";
        $prompt .= "ACTIVE DATABASE SCHEMA (MANDATORY TARGET BLUEPRINT)\n";
        $prompt .= "=======================================================\n";
        $prompt .= "Thoroughly inspect the tables, columns, and relationships defined below. All generated queries MUST align with this schema:\n\n";

        if (!empty($schemaData) && is_array($schemaData)) {
            foreach ($schemaData as$table => $details) {$prompt .= "TABLE: `{$table}`\n";

                // Columns with types and PKs
                if (!empty($details['columns']) && is_array($details['columns'])) {$colsFormatted = [];
                    foreach ($details['columns'] as $cName =>$cInfo) {
                        $cType = is_array($cInfo) ? ($cInfo['type'] ?? 'TEXT') :$cInfo;
                        $pkTag = (isset($details['primary_keys']) && in_array($cName,$details['primary_keys'])) ? " [PRIMARY KEY]" : "";
                        $colsFormatted[] = "   - Column: `{$cName}` ({$cType}){$pkTag}";
                    }
                    $prompt .= implode("\n", $colsFormatted) . "\n";
                }

                // Foreign Keys
                if (!empty($details['foreign_keys']) && is_array($details['foreign_keys'])) {$fks = [];
                    foreach ($details['foreign_keys'] as $fk) {$fks[] = "   - Foreign Key: `{$fk['column']}` REFERENCES `{$fk['references']}`(`{$fk['reference_column']}`)";
                    }
                    $prompt .= implode("\n", $fks) . "\n";
                }
                $prompt .= "\n";
            }
        } else {
            $prompt .= (is_string($_SESSION['schema']) ? trim($_SESSION['schema']) : print_r($_SESSION['schema'], true)) . "\n\n";
        }

        // =========================================================================
        // NATURAL LANGUAGE ANALYSIS & INTENT-TO-SCHEMA MAPPING ALGORITHM
        // =========================================================================
        $prompt .= "--- NATURAL LANGUAGE REASONING & SCHEMA MAPPING RULES ---\n";
        $prompt .= "Execute the following cognitive steps to translate the user's natural language input:\n\n";

        $prompt .= "1. INTENT CLASSIFICATION:\n";
        $prompt .= "   - 'insert', 'add', 'create new record', 'put' -> Construct an INSERT statement.\n";
        $prompt .= "   - 'update', 'change', 'set', 'modify' -> Construct an UPDATE statement with appropriate WHERE conditions.\n";
        $prompt .= "   - 'delete', 'remove' -> Construct a DELETE statement with appropriate WHERE conditions.\n";
        $prompt .= "   - 'show', 'get', 'list', 'display', 'view', 'find', 'highest', 'lowest', 'total' -> Construct a SELECT statement.\n";
        $prompt .= "   - 'add column', 'create table', 'drop' -> Construct appropriate DDL (ALTER/CREATE/DROP).\n\n";

        $prompt .= "2. COLUMN-TO-TABLE INFERENCE (CRITICAL):\n";
        $prompt .= "   - Real humans speak casually and often mention a COLUMN without explicitly naming the TABLE (e.g., 'insert 5 stop in bus_type').\n";
        $prompt .= "   - You MUST scan every table in the Active Database Schema above to find which table owns the mentioned column.\n";
        $prompt .= "   - Example: If the user says 'bus_type', search the schema, detect that 'bus_type' belongs to 'bus_info_tbl', and automatically target 'bus_info_tbl'. Do NOT fail or assume 'bus_type' is a table name.\n\n";

        $prompt .= "3. LITERAL DATA VALUE VS IDENTIFIER DISCRIMINATION:\n";
        $prompt .= "   - Distinctly separate payload values from schema entities.\n";
        $prompt .= "   - Numbers (e.g., 5, 100), names (e.g., 'Kian', 'Rhyza'), codes, or phrases (e.g., '5 stop', 'Aircon', 'Regular') are DATA VALUES to be stored or compared—they are NEVER table or column names.\n";
        $prompt .= "   - Format literal string values with proper quotes (e.g., '5 stop') and wrap numbers appropriately.\n\n";

        $prompt .= "4. RELATIONAL JOINS & MULTI-TABLE TRAVERSAL:\n";
        $prompt .= "   - When a natural language request asks for information that spans across multiple tables, analyze the declared Foreign Keys or identical ID fields.\n";
        $prompt .= "   - Automatically construct explicit 'INNER JOIN' or 'LEFT JOIN' clauses on matching keys. Do not hallucinate columns or relationships.\n\n";

        $prompt .= "5. SYNONYM & PHRASAL FLEXIBILITY:\n";
        $prompt .= "   - Map human concepts to the nearest schema attribute (e.g., 'cost'/'fare'/'price' -> map to amount/fare column; 'name'/'user' -> map to fullname/username column).\n";
        $prompt .= "   - For queries like 'highest [col]' or 'lowest [col]', apply appropriate aggregation or ORDER BY with LIMIT.\n\n";

        $prompt .= "6. ABSOLUTE ZERO REFUSAL POLICY:\n";
        $prompt .= "   - Even if the user's grammar is fragmented, colloquial, or brief, deducibly bridge the gap using the active schema.\n";
        $prompt .= "   - ALWAYS produce an executable, optimized SQL statement matching the developer's requested action.\n";
    } else {
        // Fallback kon walay schema nga gi-upload
        $prompt .= "--- NO ACTIVE SCHEMA CONTEXT ---\n";
        $prompt .= "Deduce standard, normalized relational tables and columns directly from the user's natural language input using valid " . strtoupper($dialect) . " syntax across all SQL operations.\n";
    }

    return $prompt;
}