<?php

require_once __DIR__ . "/../validator/validator.php";

/**
 * STEP 5: SQL VALIDATOR & QUALITY GATE
 * Inspects query syntax, verifies schema identifiers, validates dialect compliance,
 * and performs non-blocking safety checks.
 */
function handleValidator(array &$context): bool
{
    $sql = trim($context["sql"] ?? "");

    if (empty($sql)) {
        $context["validation"] = [
            "status"     => "INVALID",
            "confidence" => 0,
            "command"    => "UNKNOWN",
            "warnings"   => ["Empty SQL query received from generator."],
            "is_valid"   => false
        ];
        return false;
    }

    // -------------------------------------------------------------------------
    // 1. SYNTAX CLEANUP & EXTRACTION
    // -------------------------------------------------------------------------
    // Strip markdown code fences
    $sql = preg_replace('/```(?:sql)?\s*|\s*```$/i', '', $sql);
    $sql = trim($sql);

    // Extract starting SQL keyword
    $allSqlKeywords = 'SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|TRUNCATE|USE|WITH|SHOW|EXPLAIN|DESCRIBE';
    if (preg_match('/(' . $allSqlKeywords . ')\b[\s\S]*/i', $sql, $extracted)) {
        $sql = trim($extracted[0]);
    }

    // Extract primary SQL operation command
    $command = "SELECT";
    if (preg_match('/^\s*([A-Z]+)\b/i', $sql, $cmdMatch)) {
        $command = strtoupper($cmdMatch[1]);
    }

    // -------------------------------------------------------------------------
    // 2. MULTI-STATEMENT TOLERANCE (Single Statement Enforcement)
    // -------------------------------------------------------------------------
    $withoutQuotes = preg_replace("/'[^']*'|\"[^\"]*\"/", "''", $sql);
    $cleanForMulti = rtrim($withoutQuotes, "; \t\n\r\0\x0B");

    if (strpos($cleanForMulti, ';') !== false) {
        $parts = explode(';', $sql);
        $firstQuery = trim($parts[0]);
        if (!empty($firstQuery)) {
            $sql = $firstQuery . ';';
        }
    }

    // Ensure terminal semicolon
    if (!str_ends_with(trim($sql), ';')) {
        $sql .= ';';
    }

    // -------------------------------------------------------------------------
    // 3. NON-BLOCKING SAFETY INSPECTION (Quality Warnings)
    // -------------------------------------------------------------------------
    $warnings = [];

    // Check destructive operations lacking WHERE predicates
    if ($command === 'UPDATE' && !preg_match('/\bWHERE\b/i', $sql)) {
        $warnings[] = "Notice: UPDATE statement has no WHERE clause and will update all rows.";
    }

    if ($command === 'DELETE' && !preg_match('/\bWHERE\b/i', $sql)) {
        $warnings[] = "Notice: DELETE statement has no WHERE clause and will delete all rows.";
    }

    if (in_array($command, ['DROP', 'TRUNCATE'])) {
        $warnings[] = "Warning: DDL command '{$command}' detected. Ensure table dependencies are considered.";
    }

    // -------------------------------------------------------------------------
    // 4. DIALECT COMPATIBILITY CHECKS
    // -------------------------------------------------------------------------
    $dialect = strtoupper($_POST['dialect'] ?? $_SESSION['selected_dialect'] ?? 'MYSQL');

    if (str_contains($dialect, 'SQL SERVER') || str_contains($dialect, 'MSSQL')) {
        if (preg_match('/\bLIMIT\s+\d+/i', $sql)) {
            $warnings[] = "Dialect Alert: 'LIMIT' used. SQL Server natively requires 'SELECT TOP n'.";
        }
    } elseif (str_contains($dialect, 'MYSQL') || str_contains($dialect, 'POSTGRES') || str_contains($dialect, 'SQLITE')) {
        if (preg_match('/\bSELECT\s+TOP\s+\d+/i', $sql)) {
            $warnings[] = "Dialect Alert: 'SELECT TOP' used. Expected pagination keyword is 'LIMIT'.";
        }
    }

    // -------------------------------------------------------------------------
    // 5. SCHEMA IDENTIFIER CROSS-VERIFICATION
    // -------------------------------------------------------------------------
    $activeSchema = $_SESSION['parsed_schema_array'] ?? $_SESSION['schema'] ?? null;
    $unmatchedTables = [];

    if (!empty($activeSchema) && is_array($activeSchema)) {
        $activeTables = array_map('strtolower', array_keys($activeSchema));

        if (preg_match_all('/\b(?:FROM|INTO|UPDATE|JOIN)\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $sql, $tableMatches)) {
            foreach ($tableMatches[1] as $referencedTable) {
                $refLower = strtolower($referencedTable);
                if (!in_array($refLower, $activeTables)) {
                    $unmatchedTables[] = $referencedTable;
                }
            }
        }
    }

    if (!empty($unmatchedTables)) {
        $warnings[] = "Schema Notice: Referenced table(s) [" . implode(', ', array_unique($unmatchedTables)) . "] not explicitly found in active schema.";
    }

    // -------------------------------------------------------------------------
    // 6. ASSEMBLE VALIDATION PAYLOAD FOR STEP 6
    // -------------------------------------------------------------------------
    $context["sql"] = $sql;
    $context["validation"] = [
        "status"            => "VALIDATED",
        "confidence"        => empty($warnings) ? 1.0 : 0.85,
        "command"           => $command,
        "warnings"          => $warnings,
        "is_valid"          => true,
        "validation_status" => "VALIDATED"
    ];

    if (function_exists('validateQuery')) {
        validateQuery($context);
    }

    return true;
}
