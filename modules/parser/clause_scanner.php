<?php

function scanClauses(array $tokens): array
{
    $clauses = [

        "select"  => [],
        "from"    => [],
        "where"   => [],
        "groupby" => [],
        "having"  => [],
        "orderby" => [],
        "limit"   => []

    ];

    $current = "select";

    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {

        $word = strtolower($tokens[$i]);

        switch ($word) {

            case "from":

                $current = "from";
                continue 2;

            case "where":

                $current = "where";
                continue 2;

            case "having":

                $current = "having";
                continue 2;

            case "limit":

                $current = "limit";
                continue 2;

            case "group":

                if (
                    isset($tokens[$i + 1]) &&
                    strtolower($tokens[$i + 1]) == "by"
                ) {

                    $current = "groupby";
                    $i++;

                    continue 2;
                }

                break;

            case "order":

                if (
                    isset($tokens[$i + 1]) &&
                    strtolower($tokens[$i + 1]) == "by"
                ) {

                    $current = "orderby";
                    $i++;

                    continue 2;
                }

                break;
        }

        $clauses[$current][] = $tokens[$i];
    }

    return $clauses;
}