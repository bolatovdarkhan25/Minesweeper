<?php

namespace App;

/**
 * Class Game
 */
class Game
{
    /**
     * @var bool
     */
    private bool $firstInput;

    /**
     * @var bool
     */
    private bool $gameOver;

    /**
     * @var bool
     */
    private bool $won;

    /**
     * @var int
     */
    private int $openedCellsCount;

    /**
     * @var Map
     */
    private Map $map;

    /**
     * @param Map $map
     */
    public function __construct(Map $map)
    {
        $this->map              = $map;
        $this->gameOver         = false;
        $this->firstInput       = true;
        $this->openedCellsCount = 0;
    }

    /**
     * @return bool
     */
    public function isFirstInput(): bool
    {
        return $this->firstInput;
    }

    /**
     * @param bool $firstInput
     */
    public function setFirstInput(bool $firstInput): void
    {
        $this->firstInput = $firstInput;
    }

    /**
     * @return bool
     */
    public function isGameOver(): bool
    {
        return $this->gameOver;
    }

    /**
     * @param bool $gameOver
     */
    public function setGameOver(bool $gameOver): void
    {
        $this->gameOver = $gameOver;
    }

    /**
     * @return bool
     */
    public function isWon(): bool
    {
        return $this->won;
    }

    /**
     * @param bool $won
     */
    public function setWon(bool $won): void
    {
        $this->won = $won;
    }

    /**
     * @return int
     */
    public function getOpenedCellsCount(): int
    {
        return $this->openedCellsCount;
    }

    /**
     * @param int $openedCells
     */
    public function setOpenedCellsCount(int $openedCellsCount): void
    {
        $this->openedCellsCount = $openedCellsCount;
    }

    /**
     * Main function
     * @return void
     */
    public function play()
    {
        $this->map->build();
        $this->map->showMap();

        while ($this->isGameOver() === false) {
            $inputLine = (string) readline('X, Y and action ("f", "u", "o") through a space: ');
            $inputArr  = explode(' ', $inputLine);

            if (Input::isCorrectInput($inputArr, $this->map) === false) {
                print("Вы ввели неверные значения\n");
                continue;
            }

            $input = new Input(((int)   $inputArr[0]) - 1, ((int)   $inputArr[1]) - 1, (string) $inputArr[2]);

            if ($this->isFirstInput() === true) {
                $this->firstInputPassed($input);
            }

            $cell = $this->map->getCellByCoordinates($input->getInputX(), $input->getInputY());

            switch ($input->getAction()) {
                case $input::ACTION_FLAG:
                    $cell->flag();
                    break;
                case $input::ACTION_UNFLAG:
                    $cell->unflag();
                    break;
                case $input::ACTION_OPEN:
                    $this->openingActions(cell: $cell, input: $input);
                    break;
                default:
                    $this->endGame(false);
            }

            if ($this->getOpenedCellsCount() === (count($this->map->getCells()) - $this->map->getBombsCount())) {
                $this->endGame(true);
            }

            $this->map->showMap();
        }
    }

    /**
     * @param Cell $cell
     * @param Input $input
     * @return void
     */
    private function openingActions(Cell $cell, Input $input)
    {
        $justOpened = $cell->open(byForce: false);

        if ($cell->getValue() === CELL_BOMB) {
            $this->endGame(false);
        } else {
            if ($justOpened === true) {
                if ($cell->getValue() === CELL_EMPTY) {
                    $this->setOpenedCellsCount(
                        openedCellsCount: $this->getOpenedCellsCount() + 1 +
                        $this->map->openNeighboursOfEmptyCellAndReturnTheirCount($cell)
                    );
                } else {
                    $this->setOpenedCellsCount($this->getOpenedCellsCount() + 1);
                }
            } else {
                $countOfNewOpened = $this->map->openNeighboursOfOpenedCellAndReturnTheirCount($cell);

                if ($countOfNewOpened === -1) {
                    $this->endGame(false);
                } else {
                    $this->setOpenedCellsCount($this->getOpenedCellsCount() + $countOfNewOpened);
                }
            }
        }
    }

    /**
     * @param Input $input
     * @return void
     */
    private function firstInputPassed(Input $input)
    {
        if ($input->getAction() === $input::ACTION_OPEN) {
            $this->map->fillBombsExceptGivenCoordinates($input->getInputX(), $input->getInputY());
            $this->map->fillValues();
            $this->setFirstInput(false);
        }
    }

    /**
     * @param bool $won
     * @return void
     */
    private function endGame(bool $won)
    {
        $this->setWon($won);

        if ($won === false) {
            $this->map->openAllBombs();
        } else {
            $this->map->flagAllBombs();
        }
        $this->setGameOver(true);
    }
}