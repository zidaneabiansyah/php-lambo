<?php

class TemperatureConverter
{
    public function celsiusToFahrenheit(float $celsius): float
    {
        return round(($celsius * 9 / 5) + 32, 2);
    }

    public function fahrenheitToCelsius(float $fahrenheit): float
    {
        return round(($fahrenheit - 32) * 5 / 9, 2);
    }

    public function celsiusToKelvin(float $celsius): float
    {
        return round($celsius + 273.15, 2);
    }

    public function isFreezing(float $temp, string $unit = 'C'): bool
    {
        return match (strtoupper($unit)) {
            'C' => $temp <= 0,
            'F' => $temp <= 32,
            'K' => $temp <= 273.15,
            default => throw new \InvalidArgumentException("Unknown unit: $unit"),
        };
    }
}
