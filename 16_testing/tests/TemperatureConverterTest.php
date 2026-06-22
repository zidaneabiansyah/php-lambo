<?php

use PHPUnit\Framework\TestCase;

class TemperatureConverterTest extends TestCase
{
    private TemperatureConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new TemperatureConverter();
    }

    /** @dataProvider celsiusToFahrenheitProvider */
    public function testCelsiusToFahrenheit(float $celsius, float $expected)
    {
        $result = $this->converter->celsiusToFahrenheit($celsius);
        $this->assertEquals($expected, $result);
    }

    public static function celsiusToFahrenheitProvider(): array
    {
        return [
            [0, 32],
            [100, 212],
            [-40, -40],
            [37, 98.6],
        ];
    }

    /** @dataProvider fahrenheitToCelsiusProvider */
    public function testFahrenheitToCelsius(float $fahrenheit, float $expected)
    {
        $result = $this->converter->fahrenheitToCelsius($fahrenheit);
        $this->assertEquals($expected, $result);
    }

    public static function fahrenheitToCelsiusProvider(): array
    {
        return [
            [32, 0],
            [212, 100],
            [-40, -40],
            [98.6, 37],
        ];
    }

    public function testCelsiusToKelvin()
    {
        $this->assertEquals(273.15, $this->converter->celsiusToKelvin(0));
        $this->assertEquals(373.15, $this->converter->celsiusToKelvin(100));
    }

    /** @dataProvider isFreezingProvider */
    public function testIsFreezing(float $temp, string $unit, bool $expected)
    {
        $this->assertEquals($expected, $this->converter->isFreezing($temp, $unit));
    }

    public static function isFreezingProvider(): array
    {
        return [
            'C below' => [-5, 'C', true],
            'C at' => [0, 'C', true],
            'C above' => [5, 'C', false],
            'F below' => [20, 'F', true],
            'F at' => [32, 'F', true],
            'F above' => [40, 'F', false],
            'K below' => [250, 'K', true],
            'K at' => [273.15, 'K', true],
            'K above' => [300, 'K', false],
        ];
    }

    public function testIsFreezingInvalidUnit()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->isFreezing(0, 'X');
    }
}
