<?php

function detectCommand($tokens)
{
    global $commands;

    $count = count($tokens);

    // Check 3-word commands
    for ($i = 0; $i < $count - 2; $i++) {
        $phrase = strtolower(
            $tokens[$i] . " " .
                $tokens[$i + 1] . " " .
                $tokens[$i + 2]
        );

        if (isset($commands[$phrase])) {
            return $commands[$phrase];
        }
    }

    // Check 2-word commands
    for ($i = 0; $i < $count - 1; $i++) {
        $phrase = strtolower(
            $tokens[$i] . " " .
                $tokens[$i + 1]
        );

        if (isset($commands[$phrase])) {
            return $commands[$phrase];
        }
    }

    // Check single word
    foreach ($tokens as $token) {
        $token = strtolower($token);

        if (isset($commands[$token])) {
            return $commands[$token];
        }
    }

    return null;
}