<?php

function detectTruncateTable($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++)
    {
        $word = strtolower($tokens[$i]);

        // truncate table student
        if (
            $word == "table" &&
            isset($tokens[$i + 1])
        ) {
            return $tokens[$i + 1];
        }

        // clear all records from student
        if (
            $word == "from" &&
            isset($tokens[$i + 1])
        ) {
            return $tokens[$i + 1];
        }

        // empty student table
        if (
            $word == "empty" &&
            isset($tokens[$i + 1]) &&
            isset($tokens[$i + 2]) &&
            strtolower($tokens[$i + 2]) == "table"
        ) {
            return $tokens[$i + 1];
        }

        // clear table student
        if (
            $word == "table" &&
            isset($tokens[$i + 1])
        ) {
            return $tokens[$i + 1];
        }
    }

    return null;
}