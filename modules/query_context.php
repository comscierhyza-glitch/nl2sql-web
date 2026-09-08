<?php

function createQueryContext($input)
{
    return [

        "input" => $input,

        "standardized" => "",

        "tokens" => [],

        "pattern" => [],

        "keywords" => [],

        "parsed" => [],

        "semantic" => [

            "tables" => [

                "original" => "",

                "resolved" => "",

                "confidence" => 0,

                "source" => ""

            ],

            "columns" => [

                "original" => [],

                "resolved" => [],

                "confidence" => [],

                "matched_by" => []

            ],

            "relationships" => [],

            "warnings" => []

        ],

        "schema" => [

            "tables" => [],

            "columns" => []

        ],

        "relationships" => [],

        "validation" => [

            "valid" => false,

            "errors" => []

        ],

        "ai" => [

            "used" => false,

            "cache" => false

        ],

        "sql" => "",

        "execution" => [

            "time" => 0,

            "middlewares" => [

                "ai" => 0,

                "tokenizer" => 0,

                "pattern" => 0,

                "parser" => 0,

                "keywords" => 0,

                "semantic" => 0,

                "validator" => 0,

                "sql_generator" => 0

            ]

        ]

    ];
}
