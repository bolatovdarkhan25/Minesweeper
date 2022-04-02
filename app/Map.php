<?php

namespace App;

use JetBrains\PhpStorm\Pure;

class Map
{
    /**
     * @var int
     */
    private int   $width;

    /**
     * @var int
     */
    private int   $length;

    /**
     * @var int
     */
    private int   $bombsCount;

    /**
     * @var Cell[]
     */
    private array $cells;

    private static bool $mapCreated = false;

    public function __construct()
    {
        $this->width      = config('app.map.width', 8);
        $this->length     = config('app.map.length', 8);
        $this->bombsCount = config('app.map.bombs_count', 8);
        $this->cells      = [];
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getBombsCount(): int
    {
        return $this->bombsCount;
    }

    public function getCells(): array
    {
        return $this->cells;
    }

    private function fillCellsInitially(): void
    {
        for ($i = 0; $i < $this->width; $i++) {
            for ($j = 0; $j < $this->length; $j++) {
                $this->cells[] = new Cell($i, $j);
            }
        }
    }

    #[Pure]
    public function getCellByCoordinates(int $x, int $y): Cell
    {
        return $this->cells[($x * $this->length) + $y];
    }

    public function buildMap(): void
    {
        if (static::$mapCreated === false) {
            $this->fillCellsInitially();
            static::$mapCreated = true;
        }
    }

    private function generateBombsPositions(int $coordinateX, int $coordinateY): array
    {
        $bombsPositions = [];

        while (count($bombsPositions) < $this->bombsCount) {
            $x = rand(0, $this->width - 1);
            $y = rand(0, $this->length - 1);

            if (($x === $coordinateX && $y === $coordinateY) || in_array(['x' => $x, 'y' => $y], $bombsPositions)) {
                continue;
            }

            $bombsPositions[] = ['x' => $x, 'y' => $y];
        }

        return $bombsPositions;
    }

    public function fillBombs(int $coordinateX, int $coordinateY): void
    {
        $bombsPositions = $this->generateBombsPositions($coordinateX, $coordinateY);

        foreach ($bombsPositions as $bombPosition) {
            $cell = $this->getCellByCoordinates($bombPosition['x'], $bombPosition['y']);
            $cell->setValue(BOMB);
        }
    }

    private function getNeighboringCellsCoordinates(int $x, int $y): array
    {
        // Nearest 8 cells around
        $coordinates = [
            ['x' => $x - 1, 'y' => $y - 1],
            ['x' => $x - 1, 'y' => $y    ],
            ['x' => $x - 1, 'y' => $y + 1],
            ['x' => $x    , 'y' => $y - 1],
            ['x' => $x    , 'y' => $y + 1],
            ['x' => $x + 1, 'y' => $y - 1],
            ['x' => $x + 1, 'y' => $y    ],
            ['x' => $x + 1, 'y' => $y + 1]
        ];

        $neighboringCellsCoordinates = [];

        foreach ($coordinates as $c) {
            if ($c['x'] >= 0 && $c['x'] < $this->width && $c['y'] >= 0 && $c['y'] < $this->length) {
                $neighboringCellsCoordinates[] = $c;
            }
        }

        return $neighboringCellsCoordinates;
    }

    public function fillValues(): void
    {
        foreach ($this->cells as $cell) {
            if ($cell->getValue() === BOMB) {
                continue;
            } else {
                $neighboringCellsCoordinates = $this->getNeighboringCellsCoordinates(
                    $cell->getCoordinateX(),
                    $cell->getCoordinateY()
                );

                $neighboringBombsCount = 0;

                foreach ($neighboringCellsCoordinates as $cellCoordinate) {
                    if ($this->getCellByCoordinates($cellCoordinate['x'], $cellCoordinate['y'])->getValue() === BOMB) {
                        $neighboringBombsCount++;
                    }
                }

                $cell->setValue(' ' . $neighboringBombsCount . ' ');
            }
        }
    }

    public function openAllBombs(): void
    {
        $this->cells = array_map(function ($cell) {
            if ($cell->getValue() === BOMB) {
                $cell->open();
            }

            return $cell;
        }, $this->cells);
    }

    // Front-end кетти уже мына жакта :D
    public function showMap(): void
    {
        for ($i = 0; $i < $this->width; $i++) {
            for ($j = 0; $j < $this->length; $j++) {
                $cell = $this->getCellByCoordinates($i, $j);

                if ($cell->getIsOpened() === true) {
                    $mapValue = $cell->getValue();
                } elseif ($cell->getIsFlagged() === true) {
                    $mapValue = FLAG;
                } else {
                    $mapValue = EMPTY_VALUE;
                }

                $coloredMapValue = $this->setColorForPrint($mapValue);

                print($coloredMapValue . MARGIN_Y);
            }
            print(MARGIN_X);
        }
    }

    private function setColorForPrint(string $value): string
    {
        if ($value === BOMB) {
            $value = "\e[0;31;41m   \e[0m";
        } elseif ($value === FLAG) {
            $value = "\e[0;30;40m   \e[0m";
        } elseif ($value === ' 0 ') {
            $value = "\e[0;34;44m   \e[0m";
        }

        return $value;
    }
}