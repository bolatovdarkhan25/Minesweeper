<?php

function env(string $key, string $defaultValue): string
{
    return $_ENV[$key] ?? $defaultValue;
}

function config(string $key, mixed $defaultValue): mixed
{
    $keysInArray    = explode('.', $key);
    $configFileName = $keysInArray[0];
    $value          = require (sprintf("config/%s.php", $configFileName));

    for ($i = 1; $i < count($keysInArray); $i++) {
        $value = $value[$keysInArray[$i]] ?? $defaultValue;
    }

    return $value;
}