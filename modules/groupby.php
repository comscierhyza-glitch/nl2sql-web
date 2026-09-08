<?php
function detectGroupBy($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count - 2; $i++) {
        if (
            strtolower($tokens[$i]) == "group" &&
            strtolower($tokens[$i + 1]) == "by"
        ) {
            return $tokens[$i + 2];
        }
    }

    return null;
}