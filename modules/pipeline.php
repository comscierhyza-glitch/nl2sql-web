<?php

require_once __DIR__ . "/query_context.php";

require_once __DIR__ . "/middlewares/ai_middleware.php";
require_once __DIR__ . "/middlewares/tokenizer_middleware.php";
require_once __DIR__ . "/middlewares/pattern_middleware.php";
require_once __DIR__ . "/middlewares/parser_middleware.php";
require_once __DIR__ . "/middlewares/semantic_middleware.php";
require_once __DIR__ . "/middlewares/validator_middleware.php";
require_once __DIR__ . "/middlewares/sql_generator_middleware.php";
require_once __DIR__ . "/middlewares/keyword_middleware.php";

function processQuery($input, $dialect = 'MySQL')
{
    $totalStart = microtime(true);
    $context = createQueryContext($input);

    // I-pass ang $dialect sa handleAI o standardizeInput
    $context['dialect'] = $dialect;

    $start = microtime(true);
    handleAI($context);
    $context["execution"]["middlewares"]["ai"] =
        round((microtime(true) - $start) * 1000, 2);

    /*
    |--------------------------------------------------------------------------
    | LEGACY RULE-BASED MODULES (DISABLED)
    |--------------------------------------------------------------------------
    | Gi-comment out ni nato kay gina-overwrite nila ang sakto nga output 
    | sa AI gamit ang kadaan nga English text parsing.
    */

    // $start = microtime(true);
    // handleTokenizer($context);
    // $context["execution"]["middlewares"]["tokenizer"] = round((microtime(true) - $start) * 1000, 2);

    // $start = microtime(true);
    // handlePattern($context);
    // $context["execution"]["middlewares"]["pattern"] = round((microtime(true) - $start) * 1000, 2);

    // $start = microtime(true);
    // handleParser($context);
    // $context["execution"]["middlewares"]["parser"] = round((microtime(true) - $start) * 1000, 2);

    // $start = microtime(true);
    // handleKeywords($context);
    // $context["execution"]["middlewares"]["keywords"] = round((microtime(true) - $start) * 1000, 2);

    // $start = microtime(true);
    // handleSemantic($context);
    // $context["execution"]["middlewares"]["semantic"] = round((microtime(true) - $start) * 1000, 2);


    /*
    |--------------------------------------------------------------------------
    | 2. VALIDATOR MODULE (Security Guard)
    |--------------------------------------------------------------------------
    | I-check niya ang SQL nga hinimo sa AI kung safe ba (SELECT, INSERT, etc.)
    */
    $start = microtime(true);
    handleValidator($context);
    $context["execution"]["middlewares"]["validator"] =
        round((microtime(true) - $start) * 1000, 2);

    /*
    |--------------------------------------------------------------------------
    | 3. SQL GENERATOR MODULE
    |--------------------------------------------------------------------------
    */
    //$start = microtime(true);
    //handleSQLGenerator($context);
    //$context["execution"]["middlewares"]["sql_generator"] =
    //round((microtime(true) - $start) * 1000, 2);

    $context["execution"]["time"] =
        round((microtime(true) - $totalStart) * 1000, 2);

    return $context;
}
