<?php

function detectValidationMode(array &$context)
{

    $hasSchema =

        !empty($_SESSION["schema"]) &&

        is_array($_SESSION["schema"]);

    if ($hasSchema) {

        $context["validation"]["mode"] = "SCHEMA";

    } else {

        $context["validation"]["mode"] = "PARSER";

    }

}