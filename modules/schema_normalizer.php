<?php

require_once __DIR__ . "/schema_mapper.php";

function normalizeSchemaWords($sentence)
{
    $words = preg_split('/\s+/', $sentence);

    foreach ($words as &$word) {

        $clean = preg_replace('/[^a-zA-Z0-9_]/', '', $word);

        $mapped = mapSchemaWord($clean);

        $word = str_replace($clean, $mapped, $word);

    }

    return implode(" ", $words);
}