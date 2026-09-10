<!-- INPUT + SQL -->
<div class="top-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: stretch;">

    <!-- LEFT -->
    <div class="card">

        <!-- 📌 FIXED HEADER WITH CLEAR BUTTON -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-weight: 700; color: #1e293b; font-size: 1.1rem;">Natural Language Input</span>

            <!-- Clear Icon Button -->
            <button type="button" onclick="clearInput()" title="Clear Input Box" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; transition: color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                <i class="fas fa-trash-alt"></i> Clear
            </button>
        </div>

        <!-- 1. BULAG NGA FORM PARA SA SQL GENERATION -->
        <form method="POST" id="sqlForm">
            <input type="hidden" name="generate" value="1">

            <?php
            // Mokuha gikan sa POST o Session (PRG friendly)
            $activeDialect = $_POST['dialect'] ?? $_SESSION['selected_dialect'] ?? 'MySQL';
            ?>

            <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <label for="dialect" style="font-weight: 600; font-size: 0.85rem; color: #475569; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-database" style="color: #2563eb;"></i> Target Dialect:
                </label>
                <select name="dialect" id="dialect" style="padding: 5px 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; background: white; color: #1e293b; font-weight: 600; outline: none; cursor: pointer;">
                    <?php $currentD = $selectedDialect ?? $activeDialect ?? 'MySQL'; ?>
                    <option value="MySQL" <?= (stripos($currentD, 'mysql') !== false) ? 'selected' : '' ?>>MySQL / MariaDB</option>
                    <option value="PostgreSQL" <?= (stripos($currentD, 'postgres') !== false) ? 'selected' : '' ?>>PostgreSQL</option>
                    <option value="SQLite" <?= (stripos($currentD, 'sqlite') !== false) ? 'selected' : '' ?>>SQLite</option>
                    <option value="Microsoft SQL Server" <?= (stripos($currentD, 'sql server') !== false || stripos($currentD, 'mssql') !== false) ? 'selected' : '' ?>>MS SQL Server</option>
                </select>
            </div>

            <textarea
                name="nl_input"
                id="nlInputBox"
                style="width: 100%; height: 330px; min-height: 330px; resize: none; font-size: 0.95rem; line-height: 1.6; padding: 14px; border-radius: 8px; border: 1.5px solid #cbd5e1; box-sizing: border-box; outline: none; transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#2563eb'"
                onblur="this.style.borderColor='#cbd5e1'"
                placeholder="Type your plain English query here..."><?= htmlspecialchars($originalInput ?? '') ?></textarea>

            <button type="submit"
                name="generate"
                class="generate-btn"
                id="generateBtn">
                <span id="btnText"><i class="fas fa-magic"></i> Generate SQL</span>
            </button>
        </form>

        <!-- 2. LAIN NGA FORM PARA SA DATABASE SCHEMA IMPORT (Dili na ma-mix sa Generate SQL) -->
        <!-- CLEAN, SLEEK & STYLED SCHEMA IMPORT BOX -->
        <div class="custom-schema-box">

            <div class="schema-title">
                <i class="fas fa-file-code"></i> Optional Database Schema (.sql)
            </div>

            <p class="schema-subtitle">Enhance column & table recognition by uploading your structure.</p>

            <form method="POST" enctype="multipart/form-data">
                <?php if (isset($_SESSION["schema"]) && !empty($_SESSION["schema"])): ?>
                    <!-- ACTIVE STATE -->
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">

                        <!-- Active Badge -->
                        <div class="schema-badge-active">
                            <i class="fas fa-check-circle"></i> Schema Active: <strong><?= htmlspecialchars($_SESSION["schema_filename"] ?? 'Loaded') ?></strong>
                        </div>

                        <!-- Redesigned Remove Button -->
                        <button type="submit" name="remove_schema" class="remove-schema-btn">
                            <i class="fas fa-trash-alt"></i> Remove Schema
                        </button>

                    </div>
                <?php else: ?>
                    <!-- UPLOAD STATE -->
                    <div class="upload-controls">
                        <label class="custom-file-label" for="sqlFileInput">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span id="fileLabelText">Choose SQL File</span>
                            <input type="file" id="sqlFileInput" name="sql_file" accept=".sql" onchange="updateFileName(this)" />
                        </label>

                        <button type="submit" name="import_sql" class="schema-upload-btn">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>

    </div>

    <!-- RIGHT COLUMN CONTAINER -->
    <div class="card" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 16px;">

        <!-- Generated SQL Header -->
        <div class="sql-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b;">Generated SQL</h3>
            <!-- SLEEK ICON ACTION BUTTONS -->
            <div style="display: flex; gap: 6px; align-items: center;">
                <!-- EDIT BUTTON -->
                <button type="button" id="editBtn" onclick="toggleEdit()" class="sql-action-btn" title="Edit SQL">
                    <i class="fas fa-pen-to-square"></i>
                </button>

                <!-- COPY BUTTON -->
                <button type="button" id="copyBtn" onclick="copySQL()" <?= empty($sql) ? 'disabled' : '' ?> class="sql-action-btn" title="Copy SQL">
                    <i class="far fa-copy"></i>
                </button>

                <!-- DOWNLOAD BUTTON -->
                <button type="button" id="downloadBtn" onclick="downloadSQL()" <?= empty($sql) ? 'disabled' : '' ?> class="sql-action-btn" title="Download SQL">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>

        <!-- GENERATED SQL DARK BOX (TALLER & SLEEK) -->
        <pre style="background: #0f172a; padding: 16px; border-radius: 8px; margin: 0; width: 100%; height: 330px; min-height: 330px; max-height: 330px; box-sizing: border-box; overflow-y: auto; text-align: left; flex-shrink: 0;"><code id="sqlOutput" class="language-sql" style="font-family: 'Consolas', 'Courier New', monospace; font-size: 14px; white-space: pre-wrap; word-break: normal;"><?= trim(!empty($sql) ? htmlspecialchars($sql) : 'Your generated SQL query will appear here...'); ?></code></pre>
        <!-- QUERY INSIGHTS & EXPLANATION CARD -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
            <h4 style="margin: 0 0 10px 0; font-size: 0.95rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-brain" style="color: #3b82f6;"></i> Query Insights & Explanation
            </h4>

            <div style="font-size: 0.85rem; color: #475569; display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <strong style="color: #1e293b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">What it means (Explanation):</strong>
                    <!-- 📌 GIDUGANG: id="queryExplanationText" -->
                    <p id="queryExplanationText" style="margin: 4px 0 0 0; color: #334155; line-height: 1.5; background: #f8fafc; padding: 10px; border-radius: 6px; border-left: 3px solid #3b82f6;">
                        <?php
                        if (!empty($sql)) {
                            $upperSql = strtoupper($sql);
                            preg_match('/FROM\s+([a-zA-Z0-9_]+)/i', $sql, $matches);
                            $tableName = $matches[1] ?? 'table';

                            if (strpos($upperSql, "SELECT") !== false) {
                                echo "This query retrieves records from the <strong>{$tableName}</strong> table";

                                if (strpos($upperSql, "WHERE") !== false) {
                                    preg_match('/WHERE\s+(.+?)(?:ORDER BY|GROUP BY|;|$)/i', $sql, $whereMatches);
                                    $condition = trim($whereMatches[1] ?? '');
                                    echo " where the condition matches: <code style='color: #2563eb;'>{$condition}</code>";
                                }

                                if (strpos($upperSql, "ORDER BY") !== false) {
                                    preg_match('/ORDER BY\s+(.+?)(?:;|$)/i', $sql, $orderMatches);
                                    $orderByCol = trim($orderMatches[1] ?? '');
                                    echo " and sorts the results by <code style='color: #2563eb;'>{$orderByCol}</code>.";
                                } elseif (strpos($upperSql, "MIN(") !== false || strpos($upperSql, "MAX(") !== false || strpos($upperSql, "AVG(") !== false || strpos($upperSql, "SUM(") !== false || strpos($upperSql, "COUNT(") !== false) {
                                    echo " using an aggregate function to compute statistical data.";
                                } else {
                                    if (strpos($upperSql, "SELECT *") !== false) {
                                        echo " and displays all available columns.";
                                    } else {
                                        echo " with specific projected columns and custom aliases.";
                                    }
                                }
                            } else {
                                echo "Executes an enterprise-ready database operation based on your natural language input.";
                            }
                        } else {
                            echo "Generate an SQL query first to see its detailed explanation and breakdown.";
                        }
                        ?>
                    </p>
                </div>

                <!-- Metrics Row with Clean Dynamic Display -->
                <div id="queryMetricsRow" style="display: <?= !empty($sql) ? 'flex' : 'none' ?>; flex-wrap: wrap; align-items: center; gap: 15px; padding-top: 10px; border-top: 1px solid #f1f5f9;">

                    <!-- Operation Badge -->
                    <div>
                        <strong>Operation:</strong>
                        <span id="badgeOperation" style="color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px; font-weight: 500;">
                            <?= htmlspecialchars($sql_command ?? 'SELECT') ?>
                        </span>
                    </div>

                    <!-- Dynamic Complexity Estimator -->
                    <?php
                    $compLevel = "LOW";
                    $compScore = 10;
                    $badgeColor = "#10b981";

                    if (!empty($sql) && strpos($sql, 'BLOCKED BY SAFETY FIREWALL') === false) {
                        $sqlUpper = strtoupper($sql);
                        if (preg_match('/\b(JOIN|INNER JOIN|LEFT JOIN|RIGHT JOIN)\b/i', $sqlUpper)) $compScore += 25;
                        if (strpos($sqlUpper, 'GROUP BY') !== false) $compScore += 20;
                        if (strpos($sqlUpper, 'HAVING') !== false) $compScore += 15;
                        if (strpos($sqlUpper, 'ORDER BY') !== false) $compScore += 10;
                        if (preg_match('/LIKE\s+[\'"]%.*?[\'"]/i', $sqlUpper)) $compScore += 25;
                        if (preg_match('/\(\s*SELECT\b/i', $sqlUpper)) $compScore += 30;

                        if ($compScore <= 25) {
                            $compLevel = "LOW";
                            $badgeColor = "#10b981";
                        } elseif ($compScore <= 60) {
                            $compLevel = "MEDIUM";
                            $badgeColor = "#f59e0b";
                        } else {
                            $compLevel = "HIGH";
                            $badgeColor = "#ef4444";
                        }
                    }
                    ?>

                    <!-- Dynamic Status Indicator -->
                    <div>
                        <strong>Status:</strong>
                        <?php if (isset($validation_status) && $validation_status === 'RESTRICTED'): ?>
                            <span id="badgeStatus" style="color: #ea580c; font-weight: 600;">
                                <i class="fas fa-exclamation-triangle"></i> Restricted / Out of Scope
                            </span>
                        <?php elseif (isset($validation_status) && ($validation_status === 'VALID' || $validation_status === 'Validated')): ?>
                            <span id="badgeStatus" style="color: #16a34a; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Validated
                            </span>
                        <?php else: ?>
                            <span id="badgeStatus" style="color: #dc2626; font-weight: 600;">
                                <i class="fas fa-times-circle"></i> Invalid / Blocked
                            </span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

<!-- FLOATING TOAST NOTIFICATION (BOTTOM-RIGHT) -->
<?php if ($executionTime > 0): ?>
    <?php
    $sqlCheck = $generatedSql ?? $sql ?? $context['sql'] ?? '';
    $isError = (strpos($sqlCheck, 'ERROR:') !== false);
    ?>
    <div class="execution-time" style="position: fixed; bottom: 24px; right: 28px; z-index: 9999; display: flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); <?= $isError ? 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;' : 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;' ?>">
        <?php if ($isError): ?>
            <i class="fas fa-times-circle" style="font-size: 1rem;"></i> Generation Failed • <?= $executionTime; ?> ms
        <?php else: ?>
            <i class="fas fa-check-circle" style="font-size: 1rem;"></i> Generated Successfully • <?= $executionTime; ?> ms
        <?php endif; ?>
    </div>
<?php endif; ?>


<!-- SUGGESTION -->

<?php if (!empty($suggestions)) { ?>

    <div class="suggestion-box">

        <strong>Suggestion</strong>

        <ul>

            <?php foreach ($suggestions as $item) { ?>

                <li><?php echo htmlspecialchars($item); ?></li>

            <?php } ?>

        </ul>

    </div>

<?php } ?>