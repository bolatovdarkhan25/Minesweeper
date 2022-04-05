<?php

use App\Map;

const BOMB          = "\e[0;97;41m B \e[0m";
const FLAG          = "\e[0;97;40m F \e[0m";
const CLOSED_CELL   = "\e[0;97;107m   \e[0m";
const EMPTY_CELL    = "\e[0;97;44m E \e[0m";
const MARGIN_X      = "\n\n";
const MARGIN_Y      = '    ';
const ACTION_FLAG   = 'f';
const ACTION_UNFLAG = 'u';
const ACTION_OPEN   = 'o';
const ACTIONS       = [
    ACTION_FLAG   => 'to flag',
    ACTION_UNFLAG => 'to unflag',
    ACTION_OPEN   => 'open'
];

spl_autoload_register();

require_once 'vendor/autoload.php';
require_once 'helpers.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$map = new Map();

leaveSpace();
$map->buildMap();
$map->showMap();

$firstInput = true;

$won = null;

$openedCells = 0;

while (true) {
    if ($won !== null) {
        break;
    }

    $input        = (string) readline('X, Y and action ("f", "u", "o") through a space: ');
    $inputArr     = explode(' ', $input);
    $justOpened   = false;

    if (!isCorrectInput($inputArr, $map)) {
        print("Вы ввели неверные значения\n");
        continue;
    }

    $x      = ((int)   $inputArr[0]) - 1;
    $y      = ((int)   $inputArr[1]) - 1;
    $action = (string) $inputArr[2];

    if ($firstInput) {
        if ($action === ACTION_OPEN) {
            $map->fillBombsExceptGivenCoordinates($x, $y);
            $map->fillValues();
            $firstInput = false;
        }
    }

    $cell = $map->getCellByCoordinates($x, $y);

    switch ($action) {
        case ACTION_FLAG:
            $cell->flag();
            break;
        case ACTION_UNFLAG:
            $cell->unflag();
            break;
        case ACTION_OPEN:
            if ($cell->getIsOpened() === false) {
                $cell->open();
                $justOpened = true;
            }
    }

    if ($action === ACTION_OPEN) {
        if ($cell->getValue() === BOMB) {
            $won = false;
            $map->openAllBombs();
        } elseif ($cell->getValue() === EMPTY_CELL && $justOpened === true) {
            $openedCells += 1 + $map->openNeighboursOfCellAndReturnCountOfOpened($cell);
        } else {
            if ($justOpened === true) {
                $openedCells++;
            } else {
                $openedCells += $map->openNeighboursOfCellAndReturnCountOfOpened($cell);
            }
        }
    }

    if ($openedCells === (count($map->getCells())) - $map->getBombsCount()) {
        $won = true;
        $map->flagAllBombs();
    }

    leaveSpace();
    $map->showMap();
}

function leaveSpace(): void
{
    print "\n\n\n\n\n\n";
}

if ($won === false) {
    echo "\e[0;31mGAME OVER *_*\e[0m\n";
} elseif ($won === true) {
    print "YOU WIN :ɔ\n";
}