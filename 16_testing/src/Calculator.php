<?php

class Calculator
{
    public function add(float $a, float $b): float
    {
        return $a + $b;
    }

    public function subtract(float $a, float $b): float
    {
        return $a - $b;
    }

    public function multiply(float $a, float $b): float
    {
        return $a * $b;
    }

    public function divide(float $a, float $b): float
    {
        if ($b === 0.0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return $a / $b;
    }

    public function factorial(int $n): int
    {
        if ($n < 0) {
            throw new \InvalidArgumentException('Negative not allowed');
        }
        return $n <= 1 ? 1 : $n * $this->factorial($n - 1);
    }
}
