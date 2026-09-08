<?php

/*
|--------------------------------------------------------------------------
| Column Helper
|--------------------------------------------------------------------------
|
| Detects multi-word column names and synonyms.
|
*/

function detectColumnPhrase(array $tokens, int &$index)
{
    static $dictionary = null;

    /*
    |--------------------------------------------------------------------------
    | Build dictionary once
    |--------------------------------------------------------------------------
    */

    if ($dictionary === null) {

        $columnSynonyms = require __DIR__ . "/../rules/column_synonyms.php";

        $dictionary = [];

        foreach ($columnSynonyms as $column => $synonyms) {

            // actual column
            $dictionary[strtolower($column)] = $column;

            foreach ($synonyms as $synonym) {

                $dictionary[strtolower($synonym)] = $column;

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Longest phrase first
    |--------------------------------------------------------------------------
    */

    for ($length = 5; $length >= 1; $length--) {

        if (($index + $length) > count($tokens)) {
            continue;
        }

        $phrase = strtolower(

            implode(
                " ",
                array_slice($tokens, $index, $length)
            )

        );

        if (isset($dictionary[$phrase])) {

            $index += ($length - 1);

            return $dictionary[$phrase];

        }

    }

    return $tokens[$index];
}