<?php
function detectJoinOn($tokens)
{
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {

        if (strtolower($tokens[$i]) == "on") {

            $condition = [];

            for ($j = $i + 1; $j < $count; $j++) {

                $word = strtolower($tokens[$j]);

                /*
                ----------------------------------------
                STOP AT NEXT SQL CLAUSE
                ----------------------------------------
                */

                if (
                    in_array($word, [

                        "where",

                        "group",
                        "having",

                        "order",
                        "sort",

                        "limit"

                    ])
                ) {
                    break;
                }

                $condition[] = $tokens[$j];
            }

            if (empty($condition)) {
                return null;
            }

            return implode(" ", $condition);
        }
    }

    return null;
}