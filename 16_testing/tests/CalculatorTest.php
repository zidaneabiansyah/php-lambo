<?php

use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    private Calculator $calc;

    protected function setUp(): void
    {
        $this->calc = new Calculator();
    }

    public function testAdd()
    {
        $this->assertEquals(5, $this->calc->add(2, 3));
        $this->assertEquals(0, $this->calc->add(-2, 2));
        $this->assertEquals(-5, $this->calc->add(-2, -3));
    }

    public function testSubtract()
    {
        $this->assertEquals(3, $this->calc->subtract(5, 2));
        $this->assertEquals(-5, $this->calc->subtract(0, 5));
    }

    public function testMultiply()
    {
        $this->assertEquals(15, $this->calc->multiply(3, 5));
        $this->assertEquals(0, $this->calc->multiply(0, 100));
    }

    public function testDivide()
    {
        $this->assertEquals(4, $this->calc->divide(12, 3));
        $this->assertEquals(2.5, $this->calc->divide(5, 2));
    }

    public function testDivideByZero()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero');
        $this->calc->divide(10, 0);
    }

    public function testAddWithFloat()
    {
        $result = $this->calc->add(0.1, 0.2);
        $this->assertEqualsWithDelta(0.3, $result, 0.0001);
    }

    public function testFactorial()
    {
        $this->assertEquals(1, $this->calc->factorial(0));
        $this->assertEquals(1, $this->calc->factorial(1));
        $this->assertEquals(2, $this->calc->factorial(2));
        $this->assertEquals(6, $this->calc->factorial(3));
        $this->assertEquals(24, $this->calc->factorial(4));
        $this->assertEquals(120, $this->calc->factorial(5));
    }

    public function testFactorialNegative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->calc->factorial(-1);
    }

    public function testAddReturnsNumeric()
    {
        $result = $this->calc->add(3, 4);
        $this->assertIsNumeric($result);
        $this->assertIsFloat($result);
    }
}
