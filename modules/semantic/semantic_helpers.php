<?php

/*
|--------------------------------------------------------------------------
| Semantic Helper Functions
|--------------------------------------------------------------------------
*/

/**
 * Normalize column names.
 *
 * first_name
 * firstname
 * First Name
 * FIRST_NAME
 *
 * ↓
 *
 * firstname
 */
function normalizeColumnName(string $column): string
{
    $column = strtolower($column);

    // remove spaces
    $column = str_replace(" ", "", $column);

    // remove underscore
    $column = str_replace("_", "", $column);

    // remove dash
    $column = str_replace("-", "", $column);

    return trim($column);
}

/**
 * Normalize table names.
 */
function normalizeTableName(string $table): string
{
    $table = strtolower($table);

    $table = str_replace(" ", "", $table);

    $table = str_replace("_", "", $table);

    return trim($table);
}

/**
 * Convert plural table names to singular.
 *
 * employees -> employee
 * departments -> department
 * products -> product
 */
function singular(string $word): string
{
    $word = strtolower(trim($word));

    if (str_ends_with($word, "ies")) {
        return substr($word, 0, -3) . "y";
    }

    if (str_ends_with($word, "es")) {
        return substr($word, 0, -2);
    }

    if (str_ends_with($word, "s")) {
        return substr($word, 0, -1);
    }

    return $word;
}
/**
 * Convert singular words to plural.
 *
 * employee -> employees
 * department -> departments
 * product -> products
 */
function plural(string $word): string
{
    $word = strtolower(trim($word));

    if (str_ends_with($word, "y")) {
        return substr($word, 0, -1) . "ies";
    }

    if (
        str_ends_with($word, "s") ||
        str_ends_with($word, "x") ||
        str_ends_with($word, "z") ||
        str_ends_with($word, "ch") ||
        str_ends_with($word, "sh")
    ) {
        return $word . "es";
    }

    return $word . "s";
}
