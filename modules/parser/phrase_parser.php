<?php

function parsePhrases(array $tokens): array
{
    /*
    |--------------------------------------------------------------------------
    | Load Rules
    |--------------------------------------------------------------------------
    */

    $operators = require __DIR__ . "/../../rules/operators.php";

    $columnSynonyms = require __DIR__ . "/../../rules/column_synonyms.php";

    /*
    |--------------------------------------------------------------------------
    | Build Dictionary
    |--------------------------------------------------------------------------
    */

    $dictionary = [];

    /*
    |--------------------------------------------------------------------------
    | Column Synonyms
    |--------------------------------------------------------------------------
    */

    foreach ($columnSynonyms as $key => $value) {

        if (is_array($value)) {

            foreach ($value as $synonym) {

                $dictionary[strtolower($synonym)] = $key;

            }

        } else {

            $dictionary[strtolower($key)] = $value;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Operators
    |--------------------------------------------------------------------------
    */

    foreach ($operators as $phrase => $operator) {

        $dictionary[strtolower($phrase)] = $operator;

    }

    /*
    |--------------------------------------------------------------------------
    | Normalize
    |--------------------------------------------------------------------------
    */

    $result = [];

    $count = count($tokens);

    $i = 0;

    while ($i < $count) {

        $matched = false;

        /*
        |--------------------------------------------------------------------------
        | Longest Phrase First
        |--------------------------------------------------------------------------
        */

        for ($length = 5; $length >= 2; $length--) {

            if ($i + $length > $count) {
                continue;
            }

            $phrase = strtolower(

                implode(" ", array_slice($tokens, $i, $length))

            );

            if (isset($dictionary[$phrase])) {

                $result[] = $dictionary[$phrase];

                $i += $length;

                $matched = true;

                break;

            }

        }

        if (!$matched) {

            $result[] = strtolower(trim($tokens[$i]));

            $i++;

        }

    }

    return $result;
}