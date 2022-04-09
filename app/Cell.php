<?php

namespace App;

class Cell
{
    private string $value;
    private bool   $isFlagged;
    private int    $coordinateX;
    private int    $coordinateY;
    private bool   $isOpened;

    public function __construct(int $coordinateX, int $coordinateY)
    {
        $this->value       = CELL_CLOSED;
        $this->isFlagged   = false;
        $this->coordinateX = $coordinateX;
        $this->coordinateY = $coordinateY;
        $this->isOpened    = false;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function setIsFlagged(bool $isFlagged): void
    {
        $this->isFlagged = $isFlagged;
    }

    public function getIsFlagged(): bool
    {
        return $this->isFlagged;
    }

    public function flag()
    {
        if ($this->isOpened !== true) {
            $this->setIsFlagged(true);
        }
    }

    public function unflag()
    {
        if ($this->isOpened !== true) {
            $this->setIsFlagged(false);
        }
    }

    public function getCoordinateX(): int
    {
        return $this->coordinateX;
    }

    public function getCoordinateY(): int
    {
        return $this->coordinateY;
    }

    public function getIsOpened(): bool
    {
        return $this->isOpened;
    }

    public function setIsOpened(bool $isOpened): void
    {
        $this->isOpened = $isOpened;
    }

    public function open(bool $byForce): bool
    {
        if ($byForce === true) {
            $this->setIsFlagged(false);
            $this->setIsOpened(true);
            return true;
        } elseif ($this->isOpened === false && $this->isFlagged === false) {
            $this->setIsOpened(true);
            return true;
        } else {
            return false;
        }
    }
}