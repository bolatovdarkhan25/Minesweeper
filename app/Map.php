<?php

namespace App;

use JetBrains\PhpStorm\Pure;

class Map
{
    const MARGIN_X      = "\n\n";
    const MARGIN_Y      = '    ';

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

    /**
     * @var bool
     */
    private static bool $mapCreated = false;

    public function __construct()
    {
        $this->width          = config('app.map.width', 8);
        $this->length         = config('app.map.length', 8);
        $this->bombsCount     = config('app.map.bombs_count', 8);
        $this->cells          = [];
        $this->bombsPositions = [[]];
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getBombsCount(): int
    {
        return $this->bombsCount;
    }

    /**
     * @return Cell[]
     */
    public function getCells(): array
    {
        return $this->cells;
    }

    /**
     * @return void
     */
    private function fillCellsInitially(): void
    {
        for ($i = 0; $i < $this->width; $i++) {
            for ($j = 0; $j < $this->length; $j++) {
                $this->cells[] = new Cell($i, $j);
            }
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @return Cell
     */
    #[Pure]
    public function getCellByCoordinates(int $x, int $y): Cell
    {
        return $this->cells[($x * $this->length) + $y];
    }

    /**
     * @return void
     */
    public function buildMap(): void
    {
        if (static::$mapCreated === false) {
            $this->fillCellsInitially();
            static::$mapCreated = true;
        }
    }

    /**
     * @param int $coordinateX
     * @param int $coordinateY
     * @return void
     */
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

    /**
     * @param int $coordinateX
     * @param int $coordinateY
     * @return void
     */
    public function fillBombsExceptGivenCoordinates(int $coordinateX, int $coordinateY): void
    {
        $this->generateBombsPositions(coordinateX: $coordinateX, coordinateY: $coordinateY);

        foreach ($this->bombsPositions as $bombPosition) {
            $cell = $this->getCellByCoordinates(x: $bombPosition['x'], y: $bombPosition['y']);
            $cell->setValue(CELL_BOMB);
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @return array
     */
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

    /**
     * @return void
     */
    public function fillValues(): void
    {
        foreach ($this->cells as $cell) {
            if ($cell->getValue() !== CELL_BOMB) {
                $neighboringCellsCoordinates = $this->getNeighboringCellsCoordinates(
                    x: $cell->getCoordinateX(),
                    y: $cell->getCoordinateY()
                );

                $neighboringBombsCount = 0;

                foreach ($neighboringCellsCoordinates as $cellCoordinate) {
                    if ($this->getCellByCoordinates(x: $cellCoordinate['x'], y: $cellCoordinate['y'])->getValue() === CELL_BOMB) {
                        $neighboringBombsCount++;
                    }
                }

                if ($neighboringBombsCount === 0) {
                    $cell->setValue(CELL_EMPTY);
                } else {
                    $cell->setValue(value: "\e[0;97;100m " . $neighboringBombsCount . " \e[0m");
                }
            }
        }
    }

    /**
     * @return void
     */
    public function openAllBombs(): void
    {
        foreach ($this->bombsPositions as $bombsPosition) {
            $cell = $this->getCellByCoordinates(x: $bombsPosition['x'], y: $bombsPosition['y']);
            $cell->open(byForce: true);
        }
    }

    /**
     * @param array $neighboringCellsCoordinates
     * @return bool
     */
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

    public function openNeighboursOfEmptyCellAndReturnTheirCount(Cell $currentCell): int
    {
        $countOfOpened = 0;
        $neighboringCellsCoordinates       = $this->getNeighboringCellsCoordinates(
            x: $currentCell->getCoordinateX(),
            y: $currentCell->getCoordinateY()
        );
        $emptyNeighbouringCellsCoordinates = [];

        foreach ($neighboringCellsCoordinates as $neighboringCellCoordinate) {
            $cell = $this->getCellByCoordinates(x: $neighboringCellCoordinate['x'], y: $neighboringCellCoordinate['y']);
            if ($cell->getIsOpened() === false) {
                $cell->open(byForce: true);
                $countOfOpened++;

                if ($cell->getValue() === CELL_EMPTY) {
                    $emptyNeighbouringCellsCoordinates[] = ['x' => $cell->getCoordinateX(), 'y' => $cell->getCoordinateY()];
                }
            }
        }

        foreach ($emptyNeighbouringCellsCoordinates as $emptyNeighbouringCellCoordinate) {
            $countOfOpened += $this->openNeighboursOfEmptyCellAndReturnTheirCount(
                $this->getCellByCoordinates(x: $emptyNeighbouringCellCoordinate['x'], y: $emptyNeighbouringCellCoordinate['y'])
            );
        }

        return $countOfOpened;
    }

    /**
     * @param Cell $currentCell
     * @return int
     */
    public function openNeighboursOfOpenedCellAndReturnTheirCount(Cell $currentCell): int
    {
        $bombOpened = false;

        $countOfOpened                     = 0;
        $neighboringCellsCoordinates       = $this->getNeighboringCellsCoordinates(
            x: $currentCell->getCoordinateX(),
            y: $currentCell->getCoordinateY()
        );
        $emptyNeighbouringCellsCoordinates = [];

        if ($this->getHasNeighbouringFlaggedCells($neighboringCellsCoordinates) === false) {
            return 0;
        }

        foreach ($neighboringCellsCoordinates as $neighboringCellCoordinate) {
            $cell = $this->getCellByCoordinates(x: $neighboringCellCoordinate['x'], y: $neighboringCellCoordinate['y']);
            if ($cell->getIsOpened() === false && $cell->getIsFlagged() === false) {
                $cell->setIsOpened(isOpened: true);

                if ($cell->getValue() === CELL_BOMB) {
                    $bombOpened = true;
                }

                if ($cell->getValue() === CELL_EMPTY) {
                    $emptyNeighbouringCellsCoordinates[] = ['x' => $cell->getCoordinateX(), 'y' => $cell->getCoordinateY()];
                }

                $countOfOpened++;
            }
        }

        foreach ($emptyNeighbouringCellsCoordinates as $emptyNeighbouringCellCoordinate) {
            $countOfOpened += $this->openNeighboursOfEmptyCellAndReturnTheirCount(
                $this->getCellByCoordinates(x: $emptyNeighbouringCellCoordinate['x'], y: $emptyNeighbouringCellCoordinate['y'])
            );
        }

        if ($bombOpened === true) {
            $countOfOpened = -1;
        }

        return $countOfOpened;
    }

    /**
     * @return void
     */
    public function flagAllBombs(): void
    {
        foreach ($this->bombsPositions as $bombsPosition) {
            $cell = $this->getCellByCoordinates(x: $bombsPosition['x'], y: $bombsPosition['y']);
            $cell->flag();
        }
    }

    /**
     * @return void
     */
    // Front-end кетти уже мына жакта :D
    public function showMap(): void
    {
        // To leave space
        print "\n\n\n\n\n\n";

        $width  = $this->width + 1;
        $length = $this->length + 1;

        for ($i = 0; $i <= $width; $i++) {
            for ($j = 0; $j <= $length; $j++) {
                if ($i === 0 || $i === $width) {
                    if ($j === $length || $j === 0) {
                        $mapValue = '   ';
                    } else {
                        $mapValue = ' ' . $j . ' ';
                    }
                } elseif ($j === 0 || $j === $length) {
                    $mapValue = ' ' . $i . ' ';
                } else {
                    $cell = $this->getCellByCoordinates(x: $i - 1, y: $j - 1);

                    if ($cell->getIsOpened() === true) {
                        $mapValue = $cell->getValue();
                    } elseif ($cell->getIsFlagged() === true) {
                        $mapValue = CELL_FLAG;
                    } else {
                        $mapValue = CELL_CLOSED;
                    }
                }

                print($mapValue . self::MARGIN_Y);
            }
            print(self::MARGIN_X);
        }
    }
}