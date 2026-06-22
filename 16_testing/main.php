<?php

echo "TESTING WITH PHPUNIT\n\n";

echo "Source files:\n";
foreach (glob(__DIR__ . '/src/*.php') as $file) {
    echo "  - " . basename($file) . "\n";
}

echo "\nTest files:\n";
foreach (glob(__DIR__ . '/tests/*Test.php') as $file) {
    echo "  - " . basename($file) . "\n";
}

echo "\nCARA MENJALANKAN TES:\n\n";

echo "1. Install PHPUnit:\n";
echo "   cd 16_testing\n";
echo "   composer install\n\n";

echo "2. Jalankan semua test:\n";
echo "   vendor/bin/phpunit\n\n";

echo "3. Jalankan test spesifik:\n";
echo "   vendor/bin/phpunit tests/CalculatorTest.php\n\n";

echo "4. Dengan filter method:\n";
echo "   vendor/bin/phpunit --filter testDivide\n\n";

echo "5. Coverage report:\n";
echo "   vendor/bin/phpunit --coverage-html coverage\n\n";

echo "6. Testdox format (readable):\n";
echo "   vendor/bin/phpunit --testdox\n\n";

echo "COVERAGE:\n\n";

echo "  CalculatorTest       - assertions, exception, floating point, setUp\n";
echo "  UserTest             - validation, state change, toArray\n";
echo "  StringUtilsTest      - data provider, static methods\n";
echo "  TemperatureConverterTest - data provider, edge cases, multiple units\n";
echo "  WeatherServiceTest   - mocking, stub, expectation, callback\n";

echo "\nSelesai belajar testing!\n";
