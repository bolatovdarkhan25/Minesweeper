<?php

namespace App;

use JetBrains\PhpStorm\Pure;

/**
 * Class Input
 */
class Input
{
    const ACTION_FLAG   = 'f';
    const ACTION_UNFLAG = 'u';
    const ACTION_OPEN   = 'o';
    const ACTIONS       = [
        self::ACTION_FLAG   => 'to flag',
        self::ACTION_UNFLAG => 'to unflag',
        self::ACTION_OPEN   => 'open'
    ];

    /**
     * @var int
     */
    private int $inputX;

    /**
     * @var int
     */
    private int $inputY;

    /**
     * @var string
     */
    private string $action;

    /**
     * @param int $inputX
     * @param int $inputY
     * @param string $action
     */
    public function __construct(int $inputX, int $inputY, string $action)
    {
        $this->inputX = $inputX;
        $this->inputY = $inputY;
        $this->action = $action;
    }

    /**
     * @return int
     */
    public function getInputX(): int
    {
        return $this->inputX;
    }

    /**
     * @return int
     */
    public function getInputY(): int
    {
        return $this->inputY;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param array $inputArr
     * @param Map $map
     * @return bool
     */
    #[Pure]
    public static function isCorrectInput(array $inputArr, Map $map): bool
    {
        $countOfInput = count($inputArr);

        if ($countOfInput < 3) {
            return false;
        }

        $x      = $inputArr[0];
        $y      = $inputArr[1];
        $action = $inputArr[2];

        if (!is_numeric($x) || !is_numeric($y) || !in_array($action, array_keys(self::ACTIONS))) {
            return false;
        }

        if ($x <= 0 || $x > $map->getWidth() || $y <= 0 || $y > $map->getLength()) {
            return false;
        }

        return true;
    }
}