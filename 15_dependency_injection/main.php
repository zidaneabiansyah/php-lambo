<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Container;
use App\Contracts\LoggerInterface;
use App\Contracts\MailerInterface;
use App\Services\Database;
use App\Services\UserRepository;
use App\Services\UserService;
use App\Services\FileLogger;
use App\Services\DatabaseLogger;
use App\Providers\AppServiceProvider;

echo "1. BASIC CONTAINER (BIND & RESOLVE)\n";

$container = new Container();

$container->bind('db.config', ['dsn' => 'mysql:host=localhost;dbname=test', 'user' => 'root']);
$container->bind(Database::class, function ($c) {
    $config = $c->get('db.config');
    return new Database($config['dsn'], $config['user']);
});

$db = $container->get(Database::class);
echo $db->query('SELECT NOW()') . "\n";

echo "\n";

echo "2. SINGLETON\n";

$container->singleton(LoggerInterface::class, FileLogger::class);
$logger1 = $container->get(LoggerInterface::class);
$logger2 = $container->get(LoggerInterface::class);
echo "Same instance: " . ($logger1 === $logger2 ? 'yes' : 'no') . "\n";

$logger1->info('App started');
echo "Log: " . $logger1->getLogs()[0] . "\n";

echo "\n";

echo "3. AUTOWIRING (AUTOMATIC RESOLUTION)\n";

$container->bind(Database::class, function ($c) {
    return new Database('sqlite::memory:', 'app');
});

$repo = $container->get(UserRepository::class);
$users = $repo->findAll();
echo "Users: {$users[0]['name']}, {$users[1]['name']}\n";

echo "\n";

echo "4. INTERFACE BINDING\n";

$c4 = new Container();
$c4->bind(LoggerInterface::class, FileLogger::class);
echo "Logger A: " . get_class($c4->get(LoggerInterface::class)) . "\n";

$c4->bind(LoggerInterface::class, DatabaseLogger::class);
$newLogger = $c4->get(LoggerInterface::class);
echo "Logger B: " . get_class($newLogger) . "\n";
$newLogger->info('Logging to database');
echo "Log: " . $newLogger->getLogs()[0] . "\n";

echo "\n";

echo "5. SERVICE PROVIDER\n";

$container2 = new Container();
$provider = new AppServiceProvider();
$provider->register($container2);

$mailer = $container2->get(MailerInterface::class);
echo $mailer->send('user@test.com', 'Hello', 'Body') . "\n";
echo "Mail sent count: {$mailer->getSentCount()}\n";

echo "\n";

echo "6. DEPENDENCY CHAIN RESOLUTION\n";

$container3 = new Container();
$provider = new AppServiceProvider();
$provider->register($container3);

$container3->bind(Database::class, function ($c) {
    return new Database('pgsql:host=localhost;dbname=app', 'admin');
});

try {
    $service = $container3->get(UserService::class);
    echo "UserService resolved\n";

    $result = $service->registerUser('Budi Santoso', 'budi@test.com');
    echo "Registered: {$result['user']['name']}\n";
    echo "Email: {$result['email_result']}\n";

    $users = $service->listUsers();
    echo "Repo users: {$users[0]['name']}\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n";

echo "7. INSTANCE BINDING\n";

$container4 = new Container();
$customLogger = new FileLogger();
$customLogger->info('Pre-configured logger');
$container4->instance(LoggerInterface::class, $customLogger);

$retrieved = $container4->get(LoggerInterface::class);
echo "Same instance: " . ($retrieved === $customLogger ? 'yes' : 'no') . "\n";

echo "\n";

echo "8. ALIAS\n";

$container5 = new Container();
$container5->bind(LoggerInterface::class, FileLogger::class);
$container5->alias(LoggerInterface::class, 'logger');

$viaAlias = $container5->get('logger');
echo "Via alias: " . get_class($viaAlias) . "\n";

echo "\nSelesai belajar dependency injection & service container!\n";
