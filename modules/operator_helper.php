<?php

function detectOperator(array $tokens, int $startIndex): ?array
{
    global $operators;

    /*
    |--------------------------------------------------------------------------
    | Cache sorted operator phrases
    |--------------------------------------------------------------------------
    */

    static $sortedOperatorPhrases = null;

    if ($sortedOperatorPhrases === null) {

        $sortedOperatorPhrases = array_keys($operators);

        usort(
            $sortedOperatorPhrases,
            function ($a, $b) {

                return substr_count($b, " ")
                    <=> substr_count($a, " ");

            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Maximum words after column before operator
    |--------------------------------------------------------------------------
    */

    $maxOffset = 2;

    /*
    |--------------------------------------------------------------------------
    | Find longest matching operator
    |--------------------------------------------------------------------------
    */

    foreach ($sortedOperatorPhrases as $operatorPhrase) {

        $operatorWords = preg_split(
            '/\s+/',
            strtolower(trim($operatorPhrase))
        );

        $wordCount = count($operatorWords);

        for ($offset = 0; $offset <= $maxOffset; $offset++) {

            $matched = true;

            foreach ($operatorWords as $index => $word) {

                $token = strtolower(
                    $tokens[$startIndex + $offset + $index] ?? ""
                );

                if ($token !== $word) {

                    $matched = false;

                    break;
                }
            }

            if ($matched) {

                return [

                    "operator" => $operators[$operatorPhrase],

                    "length"   => $wordCount,

                    "offset"   => $offset

                ];
            }
        }
    }

    return null;
}