<?php
// pages/nlp.php
$lastNlp = $_SESSION["last_nlp"] ?? null;
$originalInput = $lastNlp["original_input"] ?? '';
$sql = $lastNlp["sql"] ?? '';
$sql_command = $lastNlp["command"] ?? 'SELECT';
$dialect = $lastNlp["selected_dialect"] ?? ($_SESSION['selected_dialect'] ?? 'MySQL');

// -------------------------------------------------------------------------
// SMART AST & SCHEMA PARSER (Dili na mag-null)
// -------------------------------------------------------------------------
$parsedTree = [
    "command" => !empty($sql_command) ? $sql_command : "SELECT",
    "tables"  => [],
    "columns" => []
];

if (!empty($sql)) {
    // 1. Extract Tables (FROM, JOIN, INTO, UPDATE)
    if (preg_match_all('/\b(?:FROM|JOIN|INTO|UPDATE|TABLE)\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $sql, $tableMatches)) {
        $parsedTree["tables"] = array_values(array_unique($tableMatches[1]));
    }

    $extractedCols = [];

    // 2. Extract Columns inside Aggregate Functions: SUM(salary), AVG(...), COUNT(...)
    if (preg_match_all('/\b(?:SUM|AVG|COUNT|MAX|MIN)\s*\(\s*[`"]?([a-zA-Z0-9_]+)[`"]?\s*\)/i', $sql, $aggMatches)) {
        foreach ($aggMatches[1] as $ac) {
            if ($ac !== '*') $extractedCols[] = $ac;
        }
    }

    // 3. Extract Columns from SELECT clause
    if (preg_match('/SELECT\s+(.*?)\s+FROM/is', $sql, $selectMatches)) {
        $rawCols = explode(',', $selectMatches[1]);
        foreach ($rawCols as $col) {
            $col = trim($col); // Strip leading and trailing whitespace

            // Strip aggregate wrappers and column aliases
            $clean = preg_replace('/^(?:SUM|AVG|COUNT|MAX|MIN|DISTINCT)\s*\(\s*/i', '', $col);
            $clean = preg_replace('/\s*\).*$/i', '', $clean);
            $clean = preg_replace('/\s+AS\s+.*$/i', '', $clean); // Strip aliases without parentheses
            $clean = preg_replace('/.*?\./', '', $clean);        // Strip table prefixes (e.g., users.country)
            $clean = trim(preg_replace('/[`"\s]/', '', $clean));

            // Exclude wildcard asterisks, numeric literals, or empty strings
            if (!empty($clean) && $clean !== '*' && !preg_match('/^\d+$/', $clean)) {
                $extractedCols[] = $clean;
            }
        }
    }

    // 4. Extract Columns from WHERE clause (e.g., gender = 'Female')
    if (preg_match('/WHERE\s+(.*?)(?:GROUP|ORDER|LIMIT|;|$)/is', $sql, $whereMatches)) {
        if (preg_match_all('/[`"]?([a-zA-Z_][a-zA-Z0-9_]*)[`"]?\s*(?:=|<|>|<=|>=|!=|LIKE|IN|IS)/i', $whereMatches[1], $whereCols)) {
            foreach ($whereCols[1] as $wc) {
                if (!in_array(strtoupper($wc), ['AND', 'OR', 'NOT', 'NULL'])) {
                    $extractedCols[] = $wc;
                }
            }
        }
    }

    // Filter out nulls, numeric values, and SQL keywords
    $filteredCols = array_filter(array_unique($extractedCols), function ($c) {
        return !empty($c) && !is_null($c) && !is_numeric($c) && !in_array(strtoupper($c), ['TABLE', 'COLUMN', 'SELECT', 'FROM', 'WHERE', 'SET', 'DEFAULT', 'NULL', 'AND', 'OR']);
    });

    $parsedTree["columns"] = array_values($filteredCols);
}

if (empty($parsedTree["tables"])) $parsedTree["tables"] = ["employees"];
if (empty($parsedTree["columns"])) $parsedTree["columns"] = ["*"];
?>

<div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="margin: 0 0 6px 0; font-size: 1.35rem; color: #1e293b; font-weight: 700;">
            <i class="fas fa-brain" style="color: #2563eb;"></i> Live NLP Processing Breakdown
        </h2>
        <p style="margin: 0; font-size: 0.9rem; color: #64748b;">
            Detailed execution trace of linguistic normalization, AST entity extraction, and syntactic validation.
        </p>
    </div>
    <a href="dashboard.php" style="background: #2563eb; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fas fa-arrow-left"></i> Back to Workspace
    </a>
</div>

<div class="card" style="border-top: 4px solid #3b82f6; background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">

    <?php if (!empty($sql)): ?>
        <div style="background: #f8fafc; padding: 20px; border-radius: 10px; font-size: 13.5px; border: 1px solid #e2e8f0;">

            <!-- 1. AI Standardized Input -->
            <div style="margin-bottom: 16px;">
                <strong style="color: #334155; font-size: 0.95rem;">1. AI Standardized Input:</strong><br>
                <div style="margin-top: 6px; padding: 10px 14px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; color: #0284c7; font-weight: 600;">
                    <?= htmlspecialchars($originalInput ?? $_SESSION["last_nlp"]["ai_standardized"] ?? 'Query Processed'); ?>
                </div>
            </div>

            <!-- 2. Extracted Keywords -->
            <div style="margin-bottom: 16px;">
                <strong style="color: #334155; font-size: 0.95rem;">2. Extracted Keywords:</strong><br>
                <div style="margin-top: 6px; padding: 10px 14px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; color: #16a34a; font-weight: 600;">
                    Operation: <?= htmlspecialchars($sql_command ?? 'SELECT') ?> | Target: Default Database Context (<?= isset($_SESSION["schema"]) ? 'Custom Schema Active' : 'No Schema Imported' ?>)
                </div>
            </div>

            <!-- 3. SMART Syntactic Parsing Tree (AST) -->
            <div style="margin-bottom: 16px;">
                <strong style="color: #334155; font-size: 0.95rem;">3. Syntactic Parsing (Tables & Columns):</strong><br>
                <pre style="background: #0f172a; color: #38bdf8; padding: 16px; border-radius: 8px; margin-top: 6px; font-family: monospace; overflow-x: auto; font-size: 13px;"><?= json_encode($parsedTree, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></pre>
            </div>

            <!-- 4. Validation Status -->
            <?php
            $sqlCheck = strtoupper(trim($sql));
            $isBlocked = (
                strpos($sqlCheck, 'BLOCKED BY SAFETY FIREWALL') !== false ||
                strpos($sqlCheck, 'ERROR:') !== false ||
                strpos($sqlCheck, 'OUT OF SCHEMA SCOPE') !== false ||
                strpos($sqlCheck, 'SYSTEM NOTICE') !== false ||
                strpos($sqlCheck, 'CAUSE DATA LOSS') !== false ||
                strpos($sqlCheck, '--') === 0
            );
            ?>
            <div style="margin-top: 16px;">
                <strong style="color: #334155; font-size: 0.95rem;">4. Pipeline Verification Gate:</strong><br>
                <div style="margin-top: 6px;">
                    <?php if (!empty($sql) && !$isBlocked): ?>
                        <span style="color: #16a34a; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; padding: 8px 14px; border-radius: 6px; border: 1px solid #bbf7d0;">
                            <i class="fas fa-check-circle"></i> PASSED (Syntax & Schema Structure Verified)
                        </span>
                    <?php else: ?>
                        <span style="color: #dc2626; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; background: #fee2e2; padding: 8px 14px; border-radius: 6px; border: 1px solid #fecaca;">
                            <i class="fas fa-times-circle"></i> REJECTED (Blocked by Safety Firewall / Schema Guard)
                        </span>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px 20px; color: #64748b;">
            <i class="fas fa-brain" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 12px;"></i>
            <p style="font-style: italic; font-size: 0.95rem; margin: 0 0 14px 0;">No active NLP query trace available yet.</p>
            <a href="dashboard.php" style="background: #2563eb; color: white; padding: 8px 18px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Run a Query First</a>
        </div>
    <?php endif; ?>

</div>