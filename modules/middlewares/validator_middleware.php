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
    // 1. UNIVERSAL EXTRACTION & CLEANUP
    // -------------------------------------------------------------------------
    // Strip markdown code fences
    $sql = preg_replace('/```sql\n?|```\n?/i', '', $sql);
    $sql = trim($sql);

    $allSqlKeywords = 'SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|TRUNCATE|USE|WITH|SHOW|EXPLAIN|DESCRIBE|GRANT|REVOKE|BEGIN|START|COMMIT|ROLLBACK|SAVEPOINT|EXEC|EXECUTE|CALL|PRAGMA|MERGE|VACUUM|COPY|DECLARE|SET|REINDEX|ANALYZE';

    if (preg_match('/(' . $allSqlKeywords . ')\b[\s\S]*/i', $sql, $extracted)) {
        $sql = trim($extracted[0]);
    }

    // -------------------------------------------------------------------------
    // 2. SMART MULTI-STATEMENT TOLERANCE (Dili sobra ka estrikto)
    // -------------------------------------------------------------------------
    // Tangtanga ang mga text sulod sa single/double quotes aron dili masaypan ang ';' nga naa sulod sa data
    $withoutQuotes = preg_replace("/'[^']*'|\"[^\"]*\"/", "''", $sql);
    $cleanForMulti = rtrim($withoutQuotes, "; \t\n\r\0\x0B");

    // Susiha kon aduna bay tinuod nga dangerous stacked query (sama sa ; DROP TABLE, ; DELETE FROM)
    if (preg_match('/;\s*(DROP|DELETE\s+FROM|TRUNCATE|ALTER|UPDATE)\b/i', $cleanForMulti)) {
        $context["validation"] = [
            "status" => "BLOCKED: Dangerous Multi-Statement Injection",
            "confidence" => 0,
            "reason" => "Destructive stacked query detected."
        ];
        $context["sql"] = "-- BLOCKED BY SAFETY FIREWALL: Destructive multi-statement query attempt detected.";
        return false;
    }

    // Kon naay ordinaryong semicolon sa tunga tungod kay sobraan og generate ang AI,
    // kuhaa lamang ang UNANG STATEMENT imbes nga i-block dayon ang tibuok system!
    if (strpos($cleanForMulti, ';') !== false) {
        $parts = explode(';', $sql);
        $firstQuery = trim($parts[0]);
        if (!empty($firstQuery)) {
            $sql = $firstQuery . ';';
        }
    }

    // -------------------------------------------------------------------------
    // 3. PASSED FIREWALL: I-save ang SQL ug ipadayon
    // -------------------------------------------------------------------------
    $context["sql"] = $sql;

    if (function_exists('validateQuery')) {
        validateQuery($context);
    }

    return true;
}