<?php

interface Coffee
{
    public function getDescription(): string;
    public function getCost(): float;
}

class SimpleCoffee implements Coffee
{
    public function getDescription(): string
    {
        return "Coffee";
    }

    public function getCost(): float
    {
        return 10.0;
    }
}

abstract class CoffeeDecorator implements Coffee
{
    protected Coffee $coffee;

    public function __construct(Coffee $coffee)
    {
        $this->coffee = $coffee;
    }
}

class MilkDecorator extends CoffeeDecorator
{
    public function getDescription(): string
    {
        return $this->coffee->getDescription() . ", Milk";
    }

    public function getCost(): float
    {
        return $this->coffee->getCost() + 3.0;
    }
}

class SugarDecorator extends CoffeeDecorator
{
    public function getDescription(): string
    {
        return $this->coffee->getDescription() . ", Sugar";
    }

    public function getCost(): float
    {
        return $this->coffee->getCost() + 1.5;
    }
}

class WhippedCreamDecorator extends CoffeeDecorator
{
    public function getDescription(): string
    {
        return $this->coffee->getDescription() . ", Whipped Cream";
    }

    public function getCost(): float
    {
        return $this->coffee->getCost() + 5.0;
    }
}
