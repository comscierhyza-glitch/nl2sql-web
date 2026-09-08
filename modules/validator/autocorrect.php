<?php

function autoCorrect(array &$context): void
{
    /*
    |--------------------------------------------------------------------------
    | Correct Resolved Columns
    |--------------------------------------------------------------------------
    */

    if (!empty($context["semantic"]["columns"])) {

        $correctedColumns = [];

        foreach ($context["semantic"]["columns"] as $column) {

            $correctedColumns[] =
                $column["resolved"] ?? $column["original"];

        }

        if (!empty($correctedColumns)) {

            $context["parsed"]["columns"] = $correctedColumns;

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Correct Table
    |--------------------------------------------------------------------------
    */

    if (

        empty($context["parsed"]["table"]) &&

        !empty($context["semantic"]["tables"]["resolved"])

    ) {

        $context["parsed"]["table"] =

            $context["semantic"]["tables"]["resolved"];

    }

    /*
    |--------------------------------------------------------------------------
    | Correct WHERE Columns
    |--------------------------------------------------------------------------
    */

    if (!empty($context["parsed"]["where"]["conditions"])) {

        foreach (

            $context["parsed"]["where"]["conditions"]

            as

            &$condition

        ) {

            foreach (

                $context["semantic"]["columns"] ?? []

                as

                $semantic

            ) {

                if (

                    strtolower($condition["column"])

                    ==

                    strtolower($semantic["original"])

                ) {

                    $condition["column"] =

                        $semantic["resolved"];

                }

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Correct GROUP BY
    |--------------------------------------------------------------------------
    */

    if (

        !empty($context["parsed"]["groupby"])

    ) {

        foreach (

            $context["semantic"]["columns"] ?? []

            as

            $semantic

        ) {

            if (

                strtolower($context["parsed"]["groupby"])

                ==

                strtolower($semantic["original"])

            ) {

                $context["parsed"]["groupby"] =

                    $semantic["resolved"];

            }

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Correct ORDER BY
    |--------------------------------------------------------------------------
    */

    if (

        !empty($context["parsed"]["orderby"]["column"])

    ) {

        foreach (

            $context["semantic"]["columns"] ?? []

            as

            $semantic

        ) {

            if (

                strtolower($context["parsed"]["orderby"]["column"])

                ==

                strtolower($semantic["original"])

            ) {

                $context["parsed"]["orderby"]["column"] =

                    $semantic["resolved"];

            }

        }

    }

}