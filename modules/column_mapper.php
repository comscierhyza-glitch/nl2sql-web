<?php

function mapColumnWord($word)
{
    if (
        empty($_SESSION["schema"]) ||
        !is_array($_SESSION["schema"])
    ) {
        return null;
    }

    if (is_array($word)) {

        $word = $word["name"] ?? "";
    }

    $input = strtolower(trim($word));

    /*
    |--------------------------------------------------------------------------
    | Load Dictionary
    |--------------------------------------------------------------------------
    */

    $dictionary = require __DIR__ . "/../rules/column_synonyms.php";

    /*
    |--------------------------------------------------------------------------
    | Collect All Columns
    |--------------------------------------------------------------------------
    */

    $columns = [];

    foreach ($_SESSION["schema"] as $table => $tableInfo) {

        if (
            empty($tableInfo["columns"]) ||
            !is_array($tableInfo["columns"])
        ) {
            continue;
        }

        foreach ($tableInfo["columns"] as $column => $info) {

            $columns[] = [

                "table" => $table,

                "column" => $column,

                "type" => $info["type"] ?? "",

            ];
        }
    }

    $best = null;

    $highestScore = -1;

    foreach ($columns as $candidate) {

        $score = 0;

        $matchedBy = "ranking";

        $column = strtolower($candidate["column"]);

        /*
|--------------------------------------------------------------------------
| Normalize
|--------------------------------------------------------------------------
*/

        $normalizedColumn = str_replace("_", "", $column);

        $normalizedInput = str_replace("_", "", $input);

        /*
|--------------------------------------------------------------------------
| Singular / Plural
|--------------------------------------------------------------------------
*/

        $singularInput = rtrim($normalizedInput, "s");

        $singularColumn = rtrim($normalizedColumn, "s");

        if ($singularInput === $singularColumn) {

            $score += 90;

            $matchedBy = "singular_plural";
        }

        /*
        |--------------------------------------------------------------------------
        | Exact Match
        |--------------------------------------------------------------------------
        */

        if (
            $column === $input ||
            $normalizedColumn === $normalizedInput
        ) {

            $score += 100;

            $matchedBy = "exact";
        }

        /*
        |--------------------------------------------------------------------------
        | Synonym Match
        |--------------------------------------------------------------------------
        */

        if (isset($dictionary[$column])) {

            if (in_array($input, $dictionary[$column])) {

                $score += 95;

                $matchedBy = "synonym";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Starts With
        |--------------------------------------------------------------------------
        */

        if (str_starts_with($column, $input)) {

            $score += 30;
            $matchedBy = "starts_with";
        }

        /*
        |--------------------------------------------------------------------------
        | Ends With
        |--------------------------------------------------------------------------
        */

        if (str_ends_with($column, $input)) {

            $score += 20;
            $score += 20;
            $matchedBy = "ends_with";
        }

        /*
        |--------------------------------------------------------------------------
        | Contains
        |--------------------------------------------------------------------------
        */

        if (str_contains($column, $input)) {

            $score += 15;
            $matchedBy = "contains";
        }

        /*
|--------------------------------------------------------------------------
| Multi-word Match
|--------------------------------------------------------------------------
*/

        $parts = preg_split('/\s+/', $input);

        foreach ($parts as $part) {

            if (strlen($part) < 2) {
                continue;
            }

            if (str_contains($column, $part)) {

                $score += 10;

                $matchedBy = "partial";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Similarity
        |--------------------------------------------------------------------------
        */

        similar_text($normalizedColumn, $normalizedInput, $percent);

        if ($percent >= 95) {

            $score += 100;
        } elseif ($percent >= 85) {

            $score += 80;
        } elseif ($percent >= 70) {

            $score += 50;
        } elseif ($percent >= 60) {

            $score += 25;
        }

        if ($percent >= 70 && $matchedBy == "ranking") {

            $matchedBy = "similarity";
        }

        /*
        |--------------------------------------------------------------------------
        | Best Match
        |--------------------------------------------------------------------------
        */

        if ($score > $highestScore) {

            $highestScore = $score;

            $best = [

                "table" => $candidate["table"],

                "column" => $candidate["column"],

                "type" => $candidate["type"],

                "confidence" => min($score, 100),

                "matched_by" => $matchedBy,

            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Minimum Confidence
    |--------------------------------------------------------------------------
    */

    if ($highestScore < 70) {

        return null;
    }

    return $best;
}
