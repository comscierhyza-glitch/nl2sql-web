<?php

require_once __DIR__ . "/table_inference.php";
require_once __DIR__ . "/relationship_mapper.php";
require_once __DIR__ . "/ambiguity_resolver.php";
require_once __DIR__ . "/confidence_scorer.php";

function semanticAnalyze(array $parsed, array $schema): array
{
    /*
    |--------------------------------------------------------------------------
    | No imported schema
    |--------------------------------------------------------------------------
    */

    if (empty($schema)) {
        return $parsed;
    }

    /*
    |--------------------------------------------------------------------------
    | Infer Table
    |--------------------------------------------------------------------------
    */

    $parsed = inferTable($parsed, $schema);

    /*
    |--------------------------------------------------------------------------
    | Map Relationships
    |--------------------------------------------------------------------------
    */

    $parsed = mapRelationships($parsed, $schema);

    /*
    |--------------------------------------------------------------------------
    | Resolve Ambiguity
    |--------------------------------------------------------------------------
    */

    $parsed = resolveAmbiguity($parsed);

    /*
    |--------------------------------------------------------------------------
    | Confidence Score
    |--------------------------------------------------------------------------
    */

    $parsed = scoreConfidence($parsed);

    return $parsed;
}
