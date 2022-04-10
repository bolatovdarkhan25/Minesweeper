<?php

namespace App;

/**
 * Class Cell
 */
class Cell
{
    /**
     * @var string
     */
    private string $value;

    /**
     * @var bool
     */
    private bool   $isFlagged;

    /**
     * @var int
     */
    private int    $coordinateX;

    /**
     * @var int
     */
    private int    $coordinateY;

    /**
     * @var bool
     */
    private bool   $isOpened;

    /**
     * @param int $coordinateX
     * @param int $coordinateY
     */
    public function __construct(int $coordinateX, int $coordinateY)
    {
        $this->value       = CELL_CLOSED;
        $this->isFlagged   = false;
        $this->coordinateX = $coordinateX;
        $this->coordinateY = $coordinateY;
        $this->isOpened    = false;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @param bool $isFlagged
     * @return void
     */
    public function setIsFlagged(bool $isFlagged): void
    {
        $this->isFlagged = $isFlagged;
    }

    /**
     * @return bool
     */
    public function getIsFlagged(): bool
    {
        return $this->isFlagged;
    }

    /**
     * @return void
     */
    public function flag()
    {
        if ($this->getIsOpened() !== true) {
            $this->setIsFlagged(true);
        }
    }

    /**
     * @return void
     */
    public function unflag()
    {
        if ($this->getIsOpened() !== true) {
            $this->setIsFlagged(false);
        }
    }

    /**
     * @return int
     */
    public function getCoordinateX(): int
    {
        return $this->coordinateX;
    }

    /**
     * @return int
     */
    public function getCoordinateY(): int
    {
        return $this->coordinateY;
    }

    /**
     * @return bool
     */
    public function getIsOpened(): bool
    {
        return $this->isOpened;
    }

    /**
     * @param bool $isOpened
     * @return void
     */
    public function setIsOpened(bool $isOpened): void
    {
        $this->isOpened = $isOpened;
    }

    /**
     * @param bool $byForce
     * @return bool
     */
    public function open(bool $byForce): bool
    {
        if ($byForce === true) {
            $this->setIsFlagged(false);
            $this->setIsOpened(true);
            return true;
        } elseif ($this->getIsOpened() === false && $this->getIsFlagged() === false) {
            $this->setIsOpened(true);
            return true;
        } else {
            return false;
        }
    }
}