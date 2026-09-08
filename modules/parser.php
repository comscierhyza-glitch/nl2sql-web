<?php

require_once __DIR__ . "/command.php";
require_once __DIR__ . "/table.php";
require_once __DIR__ . "/columns.php";
require_once __DIR__ . "/where.php";
require_once __DIR__ . "/aggregate.php";
require_once __DIR__ . "/orderby.php";
require_once __DIR__ . "/groupby.php";
require_once __DIR__ . "/having.php";
require_once __DIR__ . "/limit.php";
require_once __DIR__ . "/join.php";
require_once __DIR__ . "/joinRelationship.php";
require_once __DIR__ . "/detectRelationshipOn.php";
require_once __DIR__ . "/joinOn.php";
require_once __DIR__ . "/insert.php";
require_once __DIR__ . "/update.php";
require_once __DIR__ . "/delete.php";
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/createTable.php";
require_once __DIR__ . "/alterTable.php";
require_once __DIR__ . "/operator_helper.php";
require_once __DIR__ . "/column_helper.php";
require_once __DIR__ . "/dropTable.php";
require_once __DIR__ . "/truncateTable.php";
require_once __DIR__ . "/parser/clause_scanner.php";
require_once __DIR__ . "/parser/phrase_parser.php";
require_once __DIR__ . "/parser/parser_helpers.php";
require_once __DIR__ . "/semantic/semantic.php";
require_once __DIR__ . "/alias.php";

/*
|--------------------------------------------------------------------------
| Load Rules
|--------------------------------------------------------------------------
*/

$commands = include __DIR__ . '/../rules/commands.php';
$clauses = include __DIR__ . '/../rules/clauses.php';
$operators = include __DIR__ . '/../rules/operators.php';
$aggregates = include __DIR__ . '/../rules/aggregates.php';
$joins = include __DIR__ . '/../rules/joins.php';
$relationships = include __DIR__ . '/../rules/relationships.php';
$stopwords = include __DIR__ . '/../rules/stopwords.php';

function parseInput($tokens)
{

    $result = [

        "command"    => null,

        "table"      => null,

        "columns"    => ["*"],

        "where"      => null,

        "aggregate"  => null,

        "aggregates" => [],

        "aliases" => [],

        "distinct" => false,

        "orderby"    => null,

        "groupby"    => null,

        "having"     => null,

        "limit"      => null,

        "join"       => null,

        "insert"     => null,

        "update"     => null,

        "delete" => null,

        "database"   => null,

        "createTable" => null,

        "alterTable" => null,

        "dropTable" => null,

        "truncateTable" => null

    ];

    /*
|--------------------------------------------------------------------------
| Scan Clauses
|--------------------------------------------------------------------------
*/

    $clauses = scanClauses($tokens);

    /*
|--------------------------------------------------------------------------
| Normalize Whole Token Stream
|--------------------------------------------------------------------------
*/

    $normalizedTokens = parsePhrases($tokens);

    /*
|--------------------------------------------------------------------------
| Normalize Multi-word Phrases
|--------------------------------------------------------------------------
*/

    $clauses["select"] = parsePhrases($clauses["select"]);

    $clauses["where"] = parsePhrases($clauses["where"]);

    $clauses["groupby"] = parsePhrases($clauses["groupby"]);

    $clauses["orderby"] = parsePhrases($clauses["orderby"]);

    $clauses["having"] = parsePhrases($clauses["having"]);


    /*
|--------------------------------------------------------------------------
| Detect Command
|--------------------------------------------------------------------------
*/

    $result["command"] = detectCommand($tokens);

    /*
|--------------------------------------------------------------------------
| Detect Table
|--------------------------------------------------------------------------
*/

    $result["table"] = detectTable(

        $tokens,

        $result["command"]

    );

    /*
|--------------------------------------------------------------------------
| Detect Aggregate
|--------------------------------------------------------------------------
*/

    $result["aggregate"] = detectAggregate(
        $tokens,
        $result["table"]
    );

    $result["aggregates"] = detectAggregates(
        $tokens,
        $result["table"]
    );

    $result["aliases"] = detectAliases($normalizedTokens);

    /*
|--------------------------------------------------------------------------
| Attach Alias to Aggregate
|--------------------------------------------------------------------------
*/

    if (
        !empty($result["aggregates"]) &&
        !empty($result["aliases"])
    ) {

        foreach ($result["aggregates"] as &$aggregate) {

            foreach ($result["aliases"] as $alias) {

                if ($alias["position"] > $aggregate["index"]) {

                    $aggregate["alias"] = $alias["alias"];

                    break;
                }
            }
        }

        unset($aggregate);
    }

    /*
|--------------------------------------------------------------------------
| Detect DISTINCT
|--------------------------------------------------------------------------
*/

    $result["distinct"] = in_array(
        "distinct",
        array_map("strtolower", $tokens)
    );

    /*
|--------------------------------------------------------------------------
| Detect Order By
|--------------------------------------------------------------------------
*/

    $result["orderby"] = detectOrderBy($tokens);

    /*
|--------------------------------------------------------------------------
| Detect Group By
|--------------------------------------------------------------------------
*/

    $result["groupby"] = detectGroupBy($tokens);

    /*
|--------------------------------------------------------------------------
| Detect Join
|--------------------------------------------------------------------------
*/

    $result["join"] = detectJoin($tokens);

    if (
        $result["join"] &&
        empty($result["join"]["on"])
    ) {

        $result["join"]["on"] =
            detectRelationshipOn(

                $result["table"],

                $result["join"]["table"]

            );
    }

    /*
|--------------------------------------------------------------------------
| Detect Where
|--------------------------------------------------------------------------
*/

    $result["where"] = detectWhere($tokens);

    /*
|--------------------------------------------------------------------------
| Detect Having
|--------------------------------------------------------------------------
*/

    $result["having"] = detectHaving($tokens);

    /*
|--------------------------------------------------------------------------
| Detect Limit
|--------------------------------------------------------------------------
*/

    $result["limit"] = detectLimit($tokens);

    /*
|--------------------------------------------------------------------------
| Detect Columns
|--------------------------------------------------------------------------
*/

    $result["columns"] = detectColumns(

        $tokens,

        $result["command"],

        $result["table"],

        $result["where"],

        $result["aggregate"],

        $result["orderby"],

        $result["groupby"],

        $result["join"],

        $normalizedTokens

    );

    if (
        !empty($result["columns"]) &&
        !empty($result["aliases"])
    ) {

        foreach ($result["columns"] as &$column) {

            // If alias already attached, skip
            if (is_array($column)) {
                continue;
            }

            foreach ($result["aliases"] as $alias) {

                $usedByAggregate = false;

                if (!empty($result["aggregates"])) {

                    foreach ($result["aggregates"] as $aggregate) {

                        if (
                            strtolower($aggregate["column"]) ==
                            strtolower($alias["target"])
                        ) {

                            $usedByAggregate = true;
                            break;
                        }
                    }
                }

                if ($usedByAggregate) {
                    continue;
                }

                if (
                    strtolower($column) ==
                    strtolower($alias["target"])
                ) {

                    $column = [

                        "name"  => $column,

                        "alias" => $alias["alias"]

                    ];

                    break;
                }
            }
        }

        unset($column);
    }


    /*
|--------------------------------------------------------------------------
| Detect Command Specific Data
|--------------------------------------------------------------------------
*/

    switch ($result["command"]) {
        case "INSERT":

            $result["insert"] = detectInsertValues($tokens);

            break;

        case "UPDATE":

            $result["update"] = detectUpdate($tokens);

            break;

        case "DELETE":

            $result["delete"] = detectDelete($tokens);

            break;

        case "CREATE DATABASE":
        case "DROP DATABASE":
        case "USE":

            $result["database"] = detectDatabase(

                $tokens,

                $result["command"]

            );

            break;

        case "CREATE TABLE":

            $result["createTable"] = detectCreateTableColumns($tokens);

            break;

        case "ALTER TABLE":

            $result["alterTable"] = detectAlterTable($tokens);

            break;

        case "DROP TABLE":

            $result["dropTable"] = detectDropTable($tokens);

            break;

        case "TRUNCATE TABLE":

            $result["truncateTable"] = detectTruncateTable($tokens);

            break;
    }

    $result["clauses"] = $clauses;

    return $result;
}
