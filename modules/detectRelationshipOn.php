<?php

require_once __DIR__ . "/joinRelationships.php";

function detectRelationshipOn($mainTable, $joinTable)
{
    global $joinRelationships;

    $key = strtolower($mainTable) . ":" . strtolower($joinTable);

    if(isset($joinRelationships[$key]))
    {
        return $joinRelationships[$key];
    }

    return null;
}