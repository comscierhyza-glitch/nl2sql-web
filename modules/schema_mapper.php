<?php

function mapSchemaWord($word)
{
    /*
    |--------------------------------------------------------------------------
    | No Imported Schema
    |--------------------------------------------------------------------------
    */

    if (
        empty($_SESSION["schema"]) ||
        !is_array($_SESSION["schema"])
    ) {
        return $word;
    }

    /*
    |--------------------------------------------------------------------------
    | Load Schema Synonyms
    |--------------------------------------------------------------------------
    */

    $dictionary = require __DIR__ . "/../rules/schema_synonyms.php";

    /*
    |--------------------------------------------------------------------------
    | Build Table Lookup
    |--------------------------------------------------------------------------
    */

    $tableLookup = [];

    foreach (array_keys($_SESSION["schema"]) as $table) {

        $tableLookup[strtolower($table)] = $table;

    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Input
    |--------------------------------------------------------------------------
    */

    $word = strtolower(trim($word));

    $normalizedWord = str_replace("_", "", $word);

    /*
    |--------------------------------------------------------------------------
    | 1. Exact Match (Fastest)
    |--------------------------------------------------------------------------
    */

    if (isset($tableLookup[$word])) {

        return $tableLookup[$word];

    }

    /*
    |--------------------------------------------------------------------------
    | 2. Normalized Match
    |--------------------------------------------------------------------------
    */

    foreach ($tableLookup as $tableLower => $originalTable) {

        if (
            str_replace("_", "", $tableLower)
            ===
            $normalizedWord
        ) {

            return $originalTable;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | 3. Singular / Plural Match
    |--------------------------------------------------------------------------
    */

    $singular = rtrim($normalizedWord, "s");

    $plural = $normalizedWord . "s";

    foreach ($tableLookup as $tableLower => $originalTable) {

        $normalizedTable = str_replace("_", "", $tableLower);

        if (
            $normalizedTable === $singular ||
            $normalizedTable === $plural
        ) {

            return $originalTable;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | 4. Synonym Match
    |--------------------------------------------------------------------------
    */

    foreach ($dictionary as $targetTable => $synonyms) {

        foreach ($synonyms as $synonym) {

            if (
                str_replace("_", "", strtolower($synonym))
                ===
                $normalizedWord
            ) {

                if (isset($tableLookup[$targetTable])) {

                    return $tableLookup[$targetTable];

                }

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | 5. Similarity Match (Last Resort)
    |--------------------------------------------------------------------------
    */

    $bestTable = null;
    $highest = 0;

    foreach ($tableLookup as $tableLower => $originalTable) {

        similar_text(
            $normalizedWord,
            str_replace("_", "", $tableLower),
            $percent
        );

        if ($percent > $highest) {

            $highest = $percent;
            $bestTable = $originalTable;

        }

    }

    if ($highest >= 75) {

        return $bestTable;

    }

    /*
    |--------------------------------------------------------------------------
    | No Match Found
    |--------------------------------------------------------------------------
    */

    return null;
}