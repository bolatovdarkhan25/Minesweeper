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

    /**
     * @var array[]
     */
    private array $bombsPositions;

    private static bool $mapCreated = false;

    public function __construct()
    {
        $this->width          = config('app.map.width', 8);
        $this->length         = config('app.map.length', 8);
        $this->bombsCount     = config('app.map.bombs_count', 8);
        $this->cells          = [];
        $this->bombsPositions = [[]];
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

    private function generateBombsPositions(int $coordinateX, int $coordinateY): void
    {
        $i = 0;
        while (count($this->bombsPositions) < $this->bombsCount) {
            $x = rand(min: 0, max: $this->width - 1);
            $y = rand(min: 0, max: $this->length - 1);

            if (($x === $coordinateX && $y === $coordinateY) || in_array(['x' => $x, 'y' => $y], $this->bombsPositions)) {
                continue;
            }

            $this->bombsPositions[$i] = ['x' => $x, 'y' => $y];
            $i++;
        }
    }

    public function fillBombsExceptGivenCoordinates(int $coordinateX, int $coordinateY): void
    {
        $this->generateBombsPositions(coordinateX: $coordinateX, coordinateY: $coordinateY);

        foreach ($this->bombsPositions as $bombPosition) {
            $cell = $this->getCellByCoordinates(x: $bombPosition['x'], y: $bombPosition['y']);
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
                    x: $cell->getCoordinateX(),
                    y: $cell->getCoordinateY()
                );

                $neighboringBombsCount = 0;

                foreach ($neighboringCellsCoordinates as $cellCoordinate) {
                    if ($this->getCellByCoordinates(x: $cellCoordinate['x'], y: $cellCoordinate['y'])->getValue() === BOMB) {
                        $neighboringBombsCount++;
                    }
                }

                if ($neighboringBombsCount === 0) {
                    $cell->setValue(EMPTY_CELL);
                } else {
                    $cell->setValue(value: "\e[0;97;100m " . $neighboringBombsCount . " \e[0m");
                }
            }
        }
    }

    public function openAllBombs(): void
    {
        foreach ($this->bombsPositions as $bombsPosition) {
            $cell = $this->getCellByCoordinates(x: $bombsPosition['x'], y: $bombsPosition['y']);
            $cell->open();
        }
    }

    #[Pure]
    private function getHasNeighbouringFlaggedCells(array $neighboringCellsCoordinates): bool
    {
        $hasFlaggedNeighbours = false;
        foreach ($neighboringCellsCoordinates as $neighboringCellCoordinate) {
            $cell = $this->getCellByCoordinates(x: $neighboringCellCoordinate['x'], y: $neighboringCellCoordinate['y']);
            if ($cell->getIsFlagged() === true) {
                $hasFlaggedNeighbours = true;
                break;
            }
        }

        return $hasFlaggedNeighbours;
    }

    public function openNeighboursOfCellAndReturnCountOfOpened(Cell $currentCell): int
    {
        if ($currentCell->getIsOpened() === false) {
            return 0;
        }

        $countOfOpened                     = 0;
        $neighboringCellsCoordinates       = $this->getNeighboringCellsCoordinates(
            x: $currentCell->getCoordinateX(),
            y: $currentCell->getCoordinateY()
        );
        $emptyNeighbouringCellsCoordinates = [];

        if (
            $currentCell->getValue() !== EMPTY_CELL &&
            $this->getHasNeighbouringFlaggedCells($neighboringCellsCoordinates) === false
        ) {
            return 0;
        }

        foreach ($neighboringCellsCoordinates as $neighboringCellCoordinate) {
            $cell = $this->getCellByCoordinates(x: $neighboringCellCoordinate['x'], y: $neighboringCellCoordinate['y']);
            if ($cell->getIsOpened() === false && $cell->getIsFlagged() === false) {
                $cell->open();
                $countOfOpened++;

                if ($cell->getValue() === EMPTY_CELL) {
                    $emptyNeighbouringCellsCoordinates[] = ['x' => $cell->getCoordinateX(), 'y' => $cell->getCoordinateY()];
                }
            }
        }

        foreach ($emptyNeighbouringCellsCoordinates as $emptyNeighbouringCellCoordinate) {
            $countOfOpened += $this->openNeighboursOfCellAndReturnCountOfOpened(
                $this->getCellByCoordinates(x: $emptyNeighbouringCellCoordinate['x'], y: $emptyNeighbouringCellCoordinate['y'])
            );
        }

        return $countOfOpened;
    }

    public function flagAllBombs(): void
    {
        foreach ($this->bombsPositions as $bombsPosition) {
            $cell = $this->getCellByCoordinates(x: $bombsPosition['x'], y: $bombsPosition['y']);
            $cell->flag();
        }
    }

    // Front-end кетти уже мына жакта :D
    public function showMap(): void
    {
        for ($i = 0; $i < $this->width; $i++) {
            for ($j = 0; $j < $this->length; $j++) {
                $cell = $this->getCellByCoordinates(x: $i, y: $j);

                if ($cell->getIsOpened() === true) {
                    $mapValue = $cell->getValue();
                } elseif ($cell->getIsFlagged() === true) {
                    $mapValue = FLAG;
                } else {
                    $mapValue = CLOSED_CELL;
                }

                print($mapValue . MARGIN_Y);
            }
            print(MARGIN_X);
        }
    }
}