<?php
function detectDatabase($tokens, $command)
{

    if (
        $command == "CREATE DATABASE" ||
        $command == "DROP DATABASE" ||
        $command == "USE"
    ) {

        $count = count($tokens);

        for ($i = 0; $i < $count - 1; $i++) {

            if (
                strtolower($tokens[$i]) == "database"
            ) {
                return $tokens[$i + 1];
            }
        }
    }

    return null;
}