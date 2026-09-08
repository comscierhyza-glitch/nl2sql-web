<?php

function generateSQL($parsed, $semantic = [])
{

    switch ($parsed["command"]) {

        case "SELECT":
            return generateSelect($parsed, $semantic);

        case "INSERT":
            return generateInsert($parsed);

        case "UPDATE":
            return generateUpdate($parsed);

        case "DELETE":
            return generateDelete($parsed);

        case "CREATE DATABASE":
            return generateCreateDatabase($parsed);

        case "DROP DATABASE":
            return generateDropDatabase($parsed);

        case "USE":
            return generateUseDatabase($parsed);

        case "CREATE TABLE":
            return generateCreateTable($parsed);

        case "ALTER TABLE":
            return generateAlterTable($parsed);

        case "DROP TABLE":
            return generateDropTable($parsed);

        case "TRUNCATE TABLE":
            return generateTruncateTable($parsed);

        default:
            return "-- SQL command not supported.";
    }
}

function generateSelect($parsed, $semantic = [])
{
    $sql = "SELECT ";

    /*
|--------------------------------------------------------------------------
| Semantic Resolution
|--------------------------------------------------------------------------
*/

    $table = $parsed["table"] ?? "";

    if (
        empty($table) &&
        !empty($parsed["semantic"]["table"])
    ) {
        $table = $parsed["semantic"]["table"];
    }

    $columns = [];

    if (!empty($semantic["columns"])) {

        foreach ($semantic["columns"] as $column) {

            if (($column["matched_by"] ?? "") === "unresolved") {
                continue;
            }

            $columns[] = $column["resolved"];
        }
    }

    if (empty($columns)) {
        $columns = $parsed["columns"];
    }

    if (!empty($parsed["distinct"])) {
        $sql .= "DISTINCT ";
    }

    /* ==========================
       COLUMNS
    ========================== */

    if (

        (empty($columns) ||

            in_array("*", $columns) ||

            in_array("all", $columns))

        &&

        empty($parsed["aggregates"])

        &&

        empty($parsed["aggregate"])

    ) {

        $sql .= "*";
    } else {
        if (
            !empty($parsed["aggregates"])
        ) {

            $selectParts = [];

            /*
----------------------------------------
NORMAL COLUMNS
----------------------------------------
*/

            foreach ($columns as $column) {

                if ($column == "*") {
                    continue;
                }

                if (is_array($column)) {

                    $selectParts[] =
                        $column["name"] .
                        " AS " .
                        $column["alias"];
                } else {

                    $selectParts[] = $column;
                }
            }

            /*
    ----------------------------------------
    GROUP BY column first
    ----------------------------------------
    */

            if (!empty($parsed["groupby"])) {

                foreach ($columns as $column) {

                    if (
                        $column != "*" &&
                        $column == $parsed["groupby"]
                    ) {

                        $selectParts[] = $column;
                    }
                }
            }

            /*
----------------------------------------
MULTIPLE AGGREGATES
----------------------------------------
*/

            foreach ($parsed["aggregates"] as $aggregate) {

                $function = strtoupper($aggregate["function"]);

                $aggregateColumn = $aggregate["column"] ?? "*";

                if ($aggregateColumn == "*") {

                    $expression = "{$function}(*)";
                } else {

                    $expression = "{$function}({$aggregateColumn})";
                }

                // ADD ALIAS
                if (!empty($aggregate["alias"])) {

                    $expression .= " AS " . $aggregate["alias"];
                }

                $selectParts[] = $expression;
            }

            $sql .= implode(", ", array_unique($selectParts));
        } elseif ($parsed["aggregate"] != null) {

            $function = strtoupper($parsed["aggregate"]["function"]);

            $aggregateColumn = $parsed["aggregate"]["column"] ?? "*";

            $selectParts = [];

            if (!empty($parsed["groupby"])) {

                foreach ($columns as $column) {

                    if (
                        $column != "*" &&
                        $column == $parsed["groupby"]
                    ) {

                        $selectParts[] = $column;
                    }
                }
            }

            if ($aggregateColumn == "*") {

                $selectParts[] = "{$function}(*)";
            } else {

                $selectParts[] = "{$function}({$aggregateColumn})";
            }

            $sql .= implode(", ", array_unique($selectParts));
        } else {

            $selectParts = [];

            foreach ($columns as $column) {

                if (is_array($column)) {

                    $selectParts[] =
                        $column["name"] .
                        " AS " .
                        $column["alias"];
                } else {

                    $selectParts[] = $column;
                }
            }

            $sql .= implode(", ", $selectParts);
        }
    }

    /* ==========================
       TABLE
    ========================== */

    if (!empty($parsed["table"])) {
        $sql .= "\nFROM " . $table;
    }

    /* ==========================
       JOINS
========================== */

    if (!empty($parsed["joins"])) {

        foreach ($parsed["joins"] as $join) {

            $sql .= "\n" . $join["type"];

            $sql .= " " . $join["table"];

            if (!empty($join["on"])) {

                $sql .= "\nON " . $join["on"];
            }
        }
    }

    /* ==========================
   WHERE
========================== */

    if (
        !empty($parsed["where"]) &&
        !empty($parsed["where"]["conditions"])
    ) {

        $conditions = $parsed["where"]["conditions"];

        /*
|--------------------------------------------------------------------------
| Semantic WHERE Column Mapping
|--------------------------------------------------------------------------
*/

        $semanticColumnMap = [];

        if (!empty($semantic["columns"])) {

            foreach ($semantic["columns"] as $item) {

                $original = strtolower($item["original"]);

                $semanticColumnMap[$original] = $item["resolved"];
            }
        }

        foreach ($conditions as $index => $condition) {

            $prefix = ($index == 0)
                ? "\nWHERE "
                : "\n" . ($condition["logic"] ?? "AND") . " ";

            $column = $condition["column"] ?? null;

            if ($column) {

                $lookup = strtolower($column);

                if (isset($semanticColumnMap[$lookup])) {

                    $column = $semanticColumnMap[$lookup];
                }
            }
            $operator = $condition["operator"] ?? null;

            if (!$column || !$operator) {
                continue;
            }

            switch ($operator) {

                case "BETWEEN":

                    $sql .= $prefix .
                        "{$column} BETWEEN '" .
                        $condition["from"] .
                        "' AND '" .
                        $condition["to"] .
                        "'";

                    break;

                case "IN":

                    $values = array_map(function ($v) {
                        return "'" . $v . "'";
                    }, $condition["value"]);

                    $sql .= $prefix .
                        "{$column} IN (" .
                        implode(", ", $values) .
                        ")";

                    break;

                case "NOT IN":

                    $values = array_map(function ($v) {
                        return "'" . $v . "'";
                    }, $condition["value"]);

                    $sql .= $prefix .
                        "{$column} NOT IN (" .
                        implode(", ", $values) .
                        ")";

                    break;

                case "LIKE":

                    $sql .= $prefix .
                        "{$column} LIKE '" .
                        $condition["value"] .
                        "'";

                    break;

                case "NOT LIKE":

                    $sql .= $prefix .
                        "{$column} NOT LIKE '{$condition["value"]}'";

                    break;

                case "IS NULL":

                    $sql .= $prefix .
                        "{$column} IS NULL";

                    break;

                case "IS NOT NULL":

                    $sql .= $prefix .
                        "{$column} IS NOT NULL";

                    break;

                default:

                    $value = $condition["value"] ?? null;

                    // Escape single quotes for SQL
                    if (!is_numeric($value)) {
                        $value = str_replace("'", "''", $value);
                    }

                    if (is_numeric($value)) {

                        $sql .= $prefix .
                            "{$column} {$operator} {$value}";
                    } else {

                        $sql .= $prefix .
                            "{$column} {$operator} '{$value}'";
                    }

                    break;
            }
        }
    }

    /* ==========================
       GROUP BY
    ========================== */

    if (!empty($parsed["groupby"])) {
        $sql .= "\nGROUP BY " . $parsed["groupby"];
    }

    /* ==========================
   HAVING
========================== */

    if (!empty($parsed["having"])) {

        $aggregate = $parsed["having"]["aggregate"] ?? null;
        $column    = $parsed["having"]["column"] ?? null;
        $operator  = $parsed["having"]["operator"] ?? null;
        $value     = $parsed["having"]["value"] ?? null;

        if ($aggregate && $operator && $value !== null) {

            // Build aggregate expression
            if ($aggregate == "COUNT") {
                $expression = "COUNT(*)";
            } else {
                $expression = "{$aggregate}({$column})";
            }

            if (is_numeric($value)) {
                $sql .= "\nHAVING {$expression} {$operator} {$value}";
            } else {
                $sql .= "\nHAVING {$expression} {$operator} '{$value}'";
            }
        }
    }

    /* ==========================
       ORDER BY
    ========================== */

    if (!empty($parsed["orderby"])) {
        $column = $parsed["orderby"]["column"];
        $direction = strtoupper($parsed["orderby"]["direction"] ?? "ASC");

        if (!empty($column)) {
            $sql .= "\nORDER BY {$column} {$direction}";
        }
    }

    /* ==========================
   LIMIT
========================== */

    if ($parsed["limit"] !== null) {
        $sql .= "\nLIMIT " . $parsed["limit"];
    }
    if (isset($parsed["limit"]) && $parsed["limit"] !== null) {

        $sql .= "\nLIMIT " . $parsed["limit"];
    }

    return $sql . ";";
}

function generateInsert($parsed)
{
    $sql = "INSERT INTO ";

    // ==========================
    // TABLE
    // ==========================

    $sql .= $parsed["table"];

    // ==========================
    // COLUMNS
    // ==========================

    if (
        !empty($parsed["insert"]["columns"])
    ) {
        $sql .= "\n(";
        $sql .= implode(", ", $parsed["insert"]["columns"]);
        $sql .= ")";
    }

    // ==========================
    // VALUES
    // ==========================

    if (
        !empty($parsed["insert"]["values"])
    ) {
        $values = [];

        foreach ($parsed["insert"]["values"] as $value) {
            if (is_numeric($value)) {
                $values[] = $value;
            } else {
                $values[] = "'" . $value . "'";
            }
        }

        $sql .= "\nVALUES";
        $sql .= "\n(";
        $sql .= implode(", ", $values);
        $sql .= ")";
    }

    return $sql . ";";
}

function generateUpdate($parsed)
{
    $sql = "UPDATE ";

    // ==========================
    // TABLE
    // ==========================

    if (!empty($parsed["table"])) {
        $sql .= $parsed["table"];
    }

    // ==========================
    // SET
    // ==========================

    if (!empty($parsed["update"])) {
        $columns = $parsed["update"]["columns"];
        $values = $parsed["update"]["values"];

        $pairs = [];

        for ($i = 0; $i < count($columns); $i++) {
            $value = $values[$i];

            if (is_numeric($value)) {
                $pairs[] = "{$columns[$i]} = {$value}";
            } else {
                $pairs[] = "{$columns[$i]} = '{$value}'";
            }
        }

        $sql .= "\nSET " . implode(", ", $pairs);
    }

    // ==========================
    // WHERE
    // ==========================

    if (!empty($parsed["where"])) {
        $column = $parsed["where"]["column"];
        $operator = $parsed["where"]["operator"];
        $value = $parsed["where"]["value"];

        if (is_numeric($value)) {
            $sql .= "\nWHERE {$column} {$operator} {$value}";
        } else {
            $sql .= "\nWHERE {$column} {$operator} '{$value}'";
        }
    }

    return $sql . ";";
}

function generateDelete($parsed)
{
    $sql = "DELETE";

    if (!empty($parsed["table"])) {
        $sql .= "\nFROM " . $parsed["table"];
    }

    /* ==========================
       JOINS
========================== */

    if (!empty($parsed["joins"])) {

        foreach ($parsed["joins"] as $join) {

            $joinType = $join["type"] ?? null;
            $joinTable = $join["table"] ?? null;
            $joinOn = $join["on"] ?? null;

            if (
                !empty($joinType) &&
                !empty($joinTable) &&
                !empty($joinOn)
            ) {

                $sql .= "\n{$joinType} {$joinTable}";
                $sql .= "\nON {$joinOn}";
            }
        }
    }

    if (!empty($parsed["where"])) {
        $column = $parsed["where"]["column"];
        $operator = $parsed["where"]["operator"];
        $value = $parsed["where"]["value"];

        switch ($operator) {
            case "LIKE":

                $sql .= "\nWHERE {$column} LIKE '{$value}'";
                break;

            case "BETWEEN":

                $sql .= "\nWHERE {$column} BETWEEN '" .
                    $parsed["where"]["from"] .
                    "' AND '" .
                    $parsed["where"]["to"] .
                    "'";

                break;

            case "IN":

                $values = array_map(function ($v) {
                    return "'" . $v . "'";
                }, $value);

                $sql .= "\nWHERE {$column} IN (" . implode(", ", $values) . ")";
                break;

            case "NOT IN":

                $values = array_map(function ($v) {
                    return "'" . $v . "'";
                }, $value);

                $sql .= "\nWHERE {$column} NOT IN (" . implode(", ", $values) . ")";
                break;

            default:

                if (is_numeric($value)) {
                    $sql .= "\nWHERE {$column} {$operator} {$value}";
                } else {
                    $sql .= "\nWHERE {$column} {$operator} '{$value}'";
                }
        }
    }

    return $sql . ";";
}

function generateCreateDatabase($parsed)
{
    $sql = "CREATE DATABASE";

    if (!empty($parsed["database"])) {
        $sql .= " " . $parsed["database"];
    }

    return $sql . ";";
}

function generateDropDatabase($parsed)
{
    $sql = "DROP DATABASE";

    if (!empty($parsed["database"])) {
        $sql .= " " . $parsed["database"];
    }

    return $sql . ";";
}

function generateUseDatabase($parsed)
{
    $sql = "USE";

    if (!empty($parsed["database"])) {
        $sql .= " " . $parsed["database"];
    }

    return $sql . ";";
}

function generateCreateTable($parsed)
{
    $sql = "CREATE TABLE ";

    $sql .= $parsed["table"] . " (\n";

    $fields = [];

    if (!empty($parsed["createTable"])) {
        foreach ($parsed["createTable"] as $column) {
            $type = $column["type"];

            // Default length sa VARCHAR
            if ($type == "VARCHAR") {
                $type = "VARCHAR(255)";
            }

            $fields[] =
                "    " .
                $column["column"] .
                " " .
                $type;
        }
    }

    $sql .= implode(",\n", $fields);

    $sql .= "\n);";

    return $sql;
}

function generateAlterTable($parsed)
{
    $sql = "ALTER TABLE ";

    $sql .= $parsed["table"];

    if (!empty($parsed["alterTable"])) {
        $action = $parsed["alterTable"]["action"];
        $column = $parsed["alterTable"]["column"];
        $type = $parsed["alterTable"]["type"];

        switch ($action) {
            case "ADD COLUMN":

                if ($type == "VARCHAR") {
                    $type = "VARCHAR(255)";
                }

                $sql .= "\nADD COLUMN {$column} {$type}";
                break;

            case "DROP COLUMN":

                $sql .= "\nDROP COLUMN {$column}";
                break;

            case "MODIFY COLUMN":

                if ($type == "VARCHAR") {
                    $type = "VARCHAR(255)";
                }

                $sql .= "\nMODIFY COLUMN {$column} {$type}";
                break;
        }
    }

    return $sql . ";";
}

function generateDropTable($parsed)
{
    $sql = "DROP TABLE ";

    if (!empty($parsed["table"])) {
        $sql .= $parsed["table"];
    }

    return $sql . ";";
}

function generateTruncateTable($parsed)
{
    $sql = "TRUNCATE TABLE ";

    if (!empty($parsed["table"])) {
        $sql .= $parsed["table"];
    }

    return $sql . ";";
}
