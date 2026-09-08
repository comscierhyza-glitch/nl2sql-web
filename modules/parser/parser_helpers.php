<?php

/*
|--------------------------------------------------------------------------
| Parser Helper Functions
|--------------------------------------------------------------------------
|
| Common helper methods used by all parser modules.
|
*/

/**
 * Case-insensitive token search.
 */
function hasToken(array $tokens, string $word): bool
{
    return in_array(
        strtolower($word),
        array_map("strtolower", $tokens)
    );
}

/**
 * Get token after a keyword.
 */
function getTokenAfter(array $tokens, string $keyword)
{
    foreach ($tokens as $index => $token) {

        if (strtolower($token) == strtolower($keyword)) {

            return $tokens[$index + 1] ?? null;
        }
    }

    return null;
}

/**
 * Check if token is a number.
 */
function isNumericToken($token): bool
{
    return is_numeric($token);
}

/**
 * Remove empty values.
 */
function cleanTokens(array $tokens): array
{
    return array_values(
        array_filter(
            $tokens,
            fn($t) => trim($t) !== ""
        )
    );
}

/**
 * Remove duplicate values.
 */
function uniqueTokens(array $tokens): array
{
    return array_values(
        array_unique($tokens)
    );
}

/**
 * Remove ignored words.
 */
function removeWords(array $tokens, array $ignored): array
{
    return array_values(

        array_filter(

            $tokens,

            function ($token) use ($ignored) {

                return !in_array(

                    strtolower($token),

                    array_map("strtolower", $ignored)

                );
            }

        )

    );
}

/**
 * Join tokens into text.
 */
function tokensToString(array $tokens): string
{
    return trim(
        implode(" ", $tokens)
    );
}

/**
 * Normalize lowercase.
 */
function normalizeTokens(array $tokens): array
{
    return array_map(
        "strtolower",
        $tokens
    );
}