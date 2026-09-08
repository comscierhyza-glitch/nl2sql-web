<?php

require_once __DIR__ . "/../validator/validator.php";

/**
 * Universal Dialect Safety Firewall Middleware
 * Allows ALL valid SQL query types across MySQL, PostgreSQL, SQLite, and MS SQL Server.
 */
function handleValidator(array &$context): bool
{
    $sql = trim($context["sql"] ?? "");

    if (empty($sql)) {
        $context["validation"] = [
            "status" => "BLOCKED",
            "confidence" => 0,
            "reason" => "Empty SQL query generated."
        ];
        return false;
    }

    // -------------------------------------------------------------------------
    // 1. UNIVERSAL EXTRACTION (SUGGESTED ALL DIALECT COMMANDS)
    // -------------------------------------------------------------------------
    $allSqlKeywords = 'SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|TRUNCATE|USE|WITH|SHOW|EXPLAIN|DESCRIBE|GRANT|REVOKE|BEGIN|START|COMMIT|ROLLBACK|SAVEPOINT|EXEC|EXECUTE|CALL|PRAGMA|MERGE|VACUUM|COPY|DECLARE|SET|REINDEX|ANALYZE';

    if (preg_match('/(' . $allSqlKeywords . ')\b[\s\S]*/i', $sql, $extracted)) {
        $sql = trim($extracted[0]);
    }

    // Strip markdown formatting symbols
    $sql = preg_replace('/```sql\n?|```\n?/i', '', $sql);
    $sql = trim($sql);

    // -------------------------------------------------------------------------
    // 2. SECURITY CHECK: Multi-Statement Injection Shield (; stacked queries)
    // -------------------------------------------------------------------------
    // I-trim ang trailing semicolons, spaces, newlines sa tumoy
    $cleanForMulti = rtrim($sql, "; \t\n\r\0\x0B");

    // Kon aduna pa'y natabilin nga semicolon SA TUNGA, actual multi-statement attack / multi-query kini!
    if (strpos($cleanForMulti, ';') !== false) {
        $context["validation"] = [
            "status" => "BLOCKED: Multi-Statement Injection",
            "confidence" => 0,
            "reason" => "Stacked queries using ';' are forbidden."
        ];
        $context["sql"] = "-- BLOCKED BY SAFETY FIREWALL: Multi-statement query attempt detected.";
        return false;
    }

    // -------------------------------------------------------------------------
    // 3. PASSED FIREWALL: Save cleaned SQL and pass to validator
    // -------------------------------------------------------------------------
    $context["sql"] = $sql;

    if (function_exists('validateQuery')) {
        validateQuery($context);
    }

    return true;
}