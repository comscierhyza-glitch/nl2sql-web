<?php

/**
 * Query Complexity & Performance Estimator
 *
 * Analyzes generated SQL statements to score execution complexity,
 * estimated performance cost, and runtime characteristics.
 */

function estimateQueryComplexity(array &$context)
{
    $sql = trim($context["sql"] ?? "");

    // 1. Default structure kon blocked, empty, o usa ka Schema Notice/Comment (--)
    if (empty($sql) || strpos($sql, 'BLOCKED BY SAFETY FIREWALL') !== false) {
        $context["complexity"] = [
            "level" => "N/A",
            "score" => 0,
            "badge_color" => "#64748b", // Gray
            "cost_description" => "Operation blocked or empty query."
        ];
        return;
    }

    // 📌 DUGANG: Handle System Notice / Out of Schema Scope Notice
    if (str_starts_with($sql, '--')) {
        $context["complexity"] = [
            "level" => "N/A",
            "score" => 0,
            "badge_color" => "#64748b", // Gray
            "factors" => ["Out-of-schema scope notice"],
            "cost_description" => "Schema notice / Non-executable comment."
        ];
        return;
    }

    $sqlUpper = strtoupper($sql);
    $score = 10; // Base score para sa simple SELECT
    $factors = [];

    // -------------------------------------------------------------------------
    // 1. Analyze JOINs Count
    // -------------------------------------------------------------------------
    preg_match_all('/\b(JOIN|INNER JOIN|LEFT JOIN|RIGHT JOIN|CROSS JOIN)\b/i', $sqlUpper, $joinMatches);
    $joinCount = count($joinMatches[0] ?? []);

    if ($joinCount === 1) {
        $score += 20;
        $factors[] = "Single JOIN operation";
    } elseif ($joinCount >= 2) {
        $score += ($joinCount * 25);
        $factors[] = "{$joinCount} Table JOINs (High relational overhead)";
    }

    // -------------------------------------------------------------------------
    // 2. Analyze Grouping & Aggregations
    // -------------------------------------------------------------------------
    if (strpos($sqlUpper, 'GROUP BY') !== false) {
        $score += 15;
        $factors[] = "GROUP BY aggregation";
    }

    if (strpos($sqlUpper, 'HAVING') !== false) {
        $score += 15;
        $factors[] = "HAVING clause filtering";
    }

    // -------------------------------------------------------------------------
    // 3. Analyze Sorting & Pagination
    // -------------------------------------------------------------------------
    if (strpos($sqlUpper, 'ORDER BY') !== false) {
        $score += 10;
        $factors[] = "Sorting via ORDER BY";
    }

    // -------------------------------------------------------------------------
    // 4. Subqueries & Un-indexed Wildcard Searches
    // -------------------------------------------------------------------------
    if (preg_match('/LIKE\s+[\'"]%.*?[\'"]/i', $sqlUpper)) {
        $score += 25;
        $factors[] = "Leading wildcard LIKE search (Full Table Scan)";
    }

    if (preg_match('/\(\s*SELECT\b/i', $sqlUpper)) {
        $score += 30;
        $factors[] = "Nested Subquery detected";
    }

    // -------------------------------------------------------------------------
    // 5. Determine Final Rating & Level
    // -------------------------------------------------------------------------
    if ($score <= 25) {
        $level = "LOW";
        $badgeColor = "#10b981"; // Green
        $costDesc = "Fast Execution (Index Lookup / Sequential Scan)";
    } elseif ($score <= 60) {
        $level = "MEDIUM";
        $badgeColor = "#f59e0b"; // Orange/Yellow
        $costDesc = "Moderate Cost (Sorting/Aggregation Overhead)";
    } else {
        $level = "HIGH";
        $badgeColor = "#ef4444"; // Red
        $costDesc = "Heavy Query (Multiple Joins / Full Table Scan)";
    }

    $context["complexity"] = [
        "level" => $level,
        "score" => min($score, 100),
        "badge_color" => $badgeColor,
        "factors" => $factors,
        "cost_description" => $costDesc
    ];
}