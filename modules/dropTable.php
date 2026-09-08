<?php

function detectDropTable($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count - 1; $i++)
    {
        if (
            strtolower($tokens[$i]) == "table"
        )
        {
            return $tokens[$i + 1];
        }
    }

    return null;
}