<?php

use App\Game;
use App\Map;

const CELL_BOMB     = "\e[0;97;41m B \e[0m";
const CELL_FLAG     = "\e[0;97;40m F \e[0m";
const CELL_CLOSED   = "\e[0;97;107m   \e[0m";
const CELL_EMPTY    = "\e[0;97;44m E \e[0m";

spl_autoload_register();

require_once 'vendor/autoload.php';
require_once 'helpers.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$map   = new Map(); // In the future going to add map size modification
$game  = new Game($map);

$game->play();

if ($game->isWon() === false) {
    echo "\e[0;31mGAME OVER *_*\e[0m\n";
} else {
    print "YOU WIN :ɔ\n";
}