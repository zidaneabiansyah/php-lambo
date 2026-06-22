<?php

interface WeatherApiClient
{
    public function fetchTemperature(string $city): float;
}

class WeatherService
{
    private WeatherApiClient $api;
    private array $cache = [];

    public function __construct(WeatherApiClient $api)
    {
        $this->api = $api;
    }

    public function getTemperature(string $city): float
    {
        if (isset($this->cache[$city])) {
            return $this->cache[$city];
        }

        $temp = $this->api->fetchTemperature($city);
        $this->cache[$city] = $temp;
        return $temp;
    }

    public function isHot(string $city, float $threshold = 30.0): bool
    {
        return $this->getTemperature($city) > $threshold;
    }

    public function getCachedCities(): array
    {
        return array_keys($this->cache);
    }
}
