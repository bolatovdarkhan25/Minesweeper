<?php

use App\Map;
use JetBrains\PhpStorm\Pure;

function env(string $key, string $defaultValue): string
{
    return $_ENV[$key] ?? $defaultValue;
}

function config(string $key, mixed $defaultValue): mixed
{
    $keysInArray    = explode('.', $key);
    $configFileName = $keysInArray[0];
    $value          = require_once (sprintf("config/%s.php", $configFileName));

    for ($i = 1; $i < count($keysInArray); $i++) {
        $value = $value[$keysInArray[$i]] ?? $defaultValue;
    }

    return $value;
}


#[Pure]
function isCorrectInput(array $inputArr, Map $map): bool {
    $countOfInput = count($inputArr);

    if ($countOfInput < 3) {
        return false;
    }

    $x      = $inputArr[0];
    $y      = $inputArr[1];
    $action = $inputArr[2];

    if (!is_numeric($x) || !is_numeric($y) || !in_array($action, array_keys(ACTIONS))) {
        return false;
    }

    if ($x <= 0 || $x > $map->getWidth() || $y <= 0 || $y > $map->getLength()) {
        return false;
    }

    return true;
}