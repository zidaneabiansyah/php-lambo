<?php

use PHPUnit\Framework\TestCase;

class WeatherServiceTest extends TestCase
{
    public function testGetTemperature()
    {
        $api = $this->createMock(WeatherApiClient::class);
        $api->method('fetchTemperature')
            ->with('Jakarta')
            ->willReturn(32.5);

        $service = new WeatherService($api);
        $this->assertEquals(32.5, $service->getTemperature('Jakarta'));
    }

    public function testCachesResult()
    {
        $api = $this->createMock(WeatherApiClient::class);
        $api->expects($this->once())
            ->method('fetchTemperature')
            ->with('Jakarta')
            ->willReturn(32.5);

        $service = new WeatherService($api);
        $service->getTemperature('Jakarta');
        $service->getTemperature('Jakarta');
    }

    public function testIsHot()
    {
        $api = $this->createMock(WeatherApiClient::class);
        $api->method('fetchTemperature')
            ->willReturnMap([
                ['Jakarta', 35.0],
                ['Bandung', 25.0],
            ]);

        $service = new WeatherService($api);
        $this->assertTrue($service->isHot('Jakarta'));
        $this->assertFalse($service->isHot('Bandung'));
    }

    public function testIsHotWithCustomThreshold()
    {
        $api = $this->createMock(WeatherApiClient::class);
        $api->method('fetchTemperature')
            ->with('Jakarta')
            ->willReturn(30.0);

        $service = new WeatherService($api);
        $this->assertFalse($service->isHot('Jakarta', 30.0));
        $this->assertTrue($service->isHot('Jakarta', 29.0));
    }

    public function testGetCachedCities()
    {
        $api = $this->createMock(WeatherApiClient::class);
        $api->method('fetchTemperature')
            ->willReturn(25.0);

        $service = new WeatherService($api);
        $this->assertEmpty($service->getCachedCities());

        $service->getTemperature('Jakarta');
        $this->assertEquals(['Jakarta'], $service->getCachedCities());

        $service->getTemperature('Bandung');
        $this->assertEquals(['Jakarta', 'Bandung'], $service->getCachedCities());
    }

    public function testMultipleCities()
    {
        $api = $this->createMock(WeatherApiClient::class);
        $api->method('fetchTemperature')
            ->willReturnCallback(function ($city) {
                return match ($city) {
                    'Jakarta' => 33.0,
                    'Bandung' => 22.0,
                    'Surabaya' => 35.0,
                    default => 28.0,
                };
            });

        $service = new WeatherService($api);

        $this->assertTrue($service->isHot('Jakarta'));
        $this->assertFalse($service->isHot('Bandung'));
        $this->assertTrue($service->isHot('Surabaya'));

        $service->getTemperature('Tokyo');
        $this->assertFalse($service->isHot('Tokyo', 30.0));
    }

    public function testApiCalledOnlyOncePerCity()
    {
        $api = $this->createMock(WeatherApiClient::class);
        $api->expects($this->exactly(2))
            ->method('fetchTemperature')
            ->willReturnMap([
                ['Jakarta', 33.0],
                ['Bandung', 22.0],
            ]);

        $service = new WeatherService($api);
        $service->getTemperature('Jakarta');
        $service->getTemperature('Bandung');
        $service->getTemperature('Jakarta');
        $service->getTemperature('Bandung');
    }
}
