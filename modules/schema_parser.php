<?php

/**
 * Robust, High-Performance SQL Schema Parser
 * Efficiently processes large database dumps without memory overflows or PCRE backtracking crashes.
 */
function parseSQLSchema($sql)
{
    $schema = [];

    // 1. Process the SQL dump line-by-line to strip out heavy data rows and INSERT statements safely
    $lines = explode("\n", $sql);
    unset($sql); // Immediately release the raw dump from memory

    $cleanDdl = '';
    $insideCreateTable = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Skip blank lines and standard single-line comments
        if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
            continue;
        }

        // Drop data-manipulation and table-locking statements to protect server memory
        if (preg_match('/^(INSERT\s+INTO|UPDATE|DELETE|LOCK\s+TABLES|UNLOCK\s+TABLES|DROP\s+TABLE)/i', $trimmed)) {
            continue;
        }

        // Detect the start of a CREATE TABLE block
        if (preg_match('/CREATE\s+TABLE/i', $trimmed)) {
            $insideCreateTable = true;
        }

        if ($insideCreateTable) {
            $cleanDdl .= $line . "\n";
            // Mark end of table definition upon hitting the closing statement semicolon
            if (str_ends_with($trimmed, ';')) {
                $insideCreateTable = false;
            }
        }
    }
    unset($lines); // Free line buffer from RAM

    // 2. Extract CREATE TABLE definitions (supports backticks, IF NOT EXISTS, and schema qualifiers)
    preg_match_all(
        '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:`?[a-zA-Z0-9_]+`?\.)?`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*[^;]*;/is',
        $cleanDdl,
        $matches,
        PREG_SET_ORDER
    );

    foreach ($matches as $tableMatch) {
        $tableName = $tableMatch[1];
        $tableBody = $tableMatch[2];

        $schema[$tableName] = [
            "columns"      => [],
            "primary_keys" => [],
            "foreign_keys" => []
        ];

        $bodyLines = preg_split('/\r\n|\r|\n/', $tableBody);

        foreach ($bodyLines as $rawLine) {
            $line = trim($rawLine);
            $line = rtrim($line, ',');

            if ($line === '' || str_starts_with($line, '--') || str_starts_with($line, '/*')) {
                continue;
            }

            // Detect Primary Keys
            if (preg_match('/PRIMARY\s+KEY\s*\((.*?)\)/i', $line, $pkMatch)) {
                $pkCols = explode(',', $pkMatch[1]);
                foreach ($pkCols as $pk) {
                    $cleanPk = trim($pk, "`'\" \t");
                    if ($cleanPk !== '') {$schema[$tableName]["primary_keys"][] = $cleanPk;
                    }
                }
                continue;
            }

            // Detect Foreign Key Constraints
            if (preg_match('/FOREIGN\s+KEY\s*\(`?([a-zA-Z0-9_]+)`?\)\s+REFERENCES\s+`?([a-zA-Z0-9_]+)`?\s*\(`?([a-zA-Z0-9_]+)`?\)/i', $line,$fkMatch)) {
                $schema[$tableName]["foreign_keys"][] = [
                    "column"           => $fkMatch[1],
                    "references"       => $fkMatch[2],
                    "reference_column" => $fkMatch[3]
                ];
                continue;
            }

            // Skip index constraints and metadata keys
            if (preg_match('/^(UNIQUE\s+KEY|KEY|INDEX|CONSTRAINT|CHECK|FULLTEXT|SPATIAL)\b/i', $line)) {
                continue;
            }

            // Extract valid column name and data type
            if (preg_match('/^`?([a-zA-Z0-9_]+)`?\s+([a-zA-Z]+(?:\([^)]+\))?)/i', $line,$colMatch)) {
                $columnName =$colMatch[1];
                $columnType = strtoupper($colMatch[2]);

                $schema[$tableName]["columns"][$columnName] = [
                    "type" => $columnType
                ];
            }
        }
    }

    return $schema;
}

/**
 * Compresses parsed database schema into a compact format optimized for AI context tokens.
 */
function compressSchemaForAI($schemaArray)
{
    if (empty($schemaArray) || !is_array($schemaArray)) {
        return "";
    }

    $output = "DATABASE SCHEMA:\n";
    foreach ($schemaArray as$tableName => $tableData) {$colList = [];
        foreach ($tableData['columns'] as$colName => $colInfo) {$isPk = in_array($colName,$tableData['primary_keys'] ?? []) ? " [PK]" : "";
            $colList[] = "{$colName} ({$colInfo['type']}){$isPk}";
        }

        $output .= "- Table: {$tableName} (Columns: " . implode(', ', $colList) . ")";

        if (!empty($tableData['foreign_keys'])) {$fks = [];
            foreach ($tableData['foreign_keys'] as$fk) {
                $fks[] = "{$fk['column']} -> {$fk['references']}.{$fk['reference_column']}";
            }
            $output .= " [Relations: " . implode(', ', $fks) . "]";
        }
        $output .= "\n";
    }

    return trim($output);
}