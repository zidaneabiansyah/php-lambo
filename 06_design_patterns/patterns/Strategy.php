<?php

interface PaymentGateway
{
    public function pay(float $amount): string;
}

class CreditCardPayment implements PaymentGateway
{
    public function pay(float $amount): string
    {
        return "Paid $amount via Credit Card";
    }
}

class PayPalPayment implements PaymentGateway
{
    public function pay(float $amount): string
    {
        return "Paid $amount via PayPal";
    }
}

class BankTransferPayment implements PaymentGateway
{
    public function pay(float $amount): string
    {
        return "Paid $amount via Bank Transfer";
    }
}

class Checkout
{
    private PaymentGateway $gateway;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function setGateway(PaymentGateway $gateway): void
    {
        $this->gateway = $gateway;
    }

    public function processOrder(float $total): string
    {
        return $this->gateway->pay($total);
    }
}
