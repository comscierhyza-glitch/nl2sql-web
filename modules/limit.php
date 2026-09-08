<?php
function detectLimit($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {

        if (strtolower($tokens[$i]) == "limit") {

            // limit 10
            if (
                isset($tokens[$i + 1]) &&
                is_numeric($tokens[$i + 1])
            ) {
                return [
                    "value" => (int)$tokens[$i + 1]
                ];
            }

            // limit to 10
            if (
                isset($tokens[$i + 2]) &&
                strtolower($tokens[$i + 1]) == "to" &&
                is_numeric($tokens[$i + 2])
            ) {
                return [
                    "value" => (int)$tokens[$i + 2]
                ];
            }

            return null;
        }
    }

    return null;
}