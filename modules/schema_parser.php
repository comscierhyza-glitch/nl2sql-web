<?php

/**
 * Robust SQL Schema Parser
 * Extracts table structures, primary keys, and foreign keys regardless of dialect or engine syntax.
 */
function parseSQLSchema($sql)
{
    $schema = [];

    // 1. Limpyohan daan ang comments ug INSERT statements
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sql = preg_replace('/#.*$/m', '', $sql);
    $sql = preg_replace('/INSERT\s+INTO\s+.*?;/is', '', $sql);

    // 2. Mas lig-on nga Regex: Mokuha sa CREATE TABLE bisan walay ENGINE= o naay IF NOT EXISTS
    preg_match_all(
        '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)(?:\s*ENGINE=[^;]*|\s*DEFAULT\s+CHARSET=[^;]*|[^;]*);/is',
        $sql,
        $matches,
        PREG_SET_ORDER
    );

    // Fallback kon simple ra ang panapos nga semicolon
    if (empty($matches)) {
        preg_match_all(
            '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?\s*\((.*?)\);/is',
            $sql,
            $matches,
            PREG_SET_ORDER
        );
    }

    foreach ($matches as $tableMatch) {
        $tableName = $tableMatch[1];
        $tableBody = $tableMatch[2];

        $schema[$tableName] = [
            "columns" => [],
            "primary_keys" => [],
            "foreign_keys" => []
        ];

        $lines = preg_split('/\r\n|\r|\n/', $tableBody);

        foreach ($lines as $line) {
            $line = trim($line);
            $line = rtrim($line, ",");

            if ($line === "") {
                continue;
            }

            // PRIMARY KEY DETECTION
            if (preg_match('/PRIMARY\s+KEY\s*\((.*?)\)/i', $line, $pkMatch)) {
                $primaryKeys = explode(",", $pkMatch[1]);
                foreach ($primaryKeys as $primaryKey) {
                    $schema[$tableName]["primary_keys"][] = trim($primaryKey, "`'\" ");
                }
                continue;
            }

            // FOREIGN KEY DETECTION
            if (
                preg_match(
                    '/FOREIGN\s+KEY\s*\(`?(\w+)`?\)\s+REFERENCES\s+`?(\w+)`?\s*\(`?(\w+)`?\)/i',
                    $line,$fkMatch
                )
            ) {
                $schema[$tableName]["foreign_keys"][] = [
                    "column" => $fkMatch[1],
                    "references" => $fkMatch[2],
                    "reference_column" => $fkMatch[3]
                ];
                continue;
            }

            // IGNORE OTHER CONSTRAINTS
            if (
                stripos($line, "UNIQUE KEY") === 0 ||
                stripos($line, "KEY ") === 0 ||
                stripos($line, "CONSTRAINT") === 0 ||
                stripos($line, "CHECK") === 0
            ) {
                continue;
            }

            // EXTRACT COLUMN NAME + DATA TYPE
            if (
                preg_match(
                    '/^`?([a-zA-Z0-9_]+)`?\s+([a-zA-Z]+(?:\([^)]+\))?)/i',
                    $line,$columnMatch
                )
            ) {
                $columnName =$columnMatch[1];
                $columnType = strtoupper($columnMatch[2]);

                $schema[$tableName]["columns"][$columnName] = [
                    "type" => $columnType
                ];
            }
        }
    }

    return $schema;
}

/**
 * I-compress ang schema ngadto sa compact format para dili mahurot ang AI tokens
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