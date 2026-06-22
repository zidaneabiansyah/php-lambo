<?php

require_once __DIR__ . '/patterns/Singleton.php';
require_once __DIR__ . '/patterns/Factory.php';
require_once __DIR__ . '/patterns/Strategy.php';
require_once __DIR__ . '/patterns/Observer.php';
require_once __DIR__ . '/patterns/Repository.php';
require_once __DIR__ . '/patterns/Adapter.php';
require_once __DIR__ . '/patterns/Decorator.php';

echo "SINGLETON\n";

$db1 = DatabaseConnection::getInstance();
$db2 = DatabaseConnection::getInstance();

echo "Same instance: " . ($db1 === $db2 ? 'yes' : 'no') . "\n";
echo $db1->query("SELECT * FROM users") . "\n";
echo $db2->query("SELECT * FROM posts") . "\n";
echo "Connection IDs match: " . ($db1->getConnectionId() === $db2->getConnectionId() ? 'yes' : 'no') . "\n";

echo "\n";

echo "FACTORY\n";

$emailNotif = NotificationFactory::create('email');
$smsNotif = NotificationFactory::create('sms');
$pushNotif = NotificationFactory::create('push');

echo $emailNotif->send("Welcome!") . "\n";
echo $smsNotif->send("OTP: 1234") . "\n";
echo $pushNotif->send("New message") . "\n";

echo "\n";

echo "STRATEGY\n";

$checkout = new Checkout(new CreditCardPayment());
echo $checkout->processOrder(250000) . "\n";

$checkout->setGateway(new PayPalPayment());
echo $checkout->processOrder(150000) . "\n";

$checkout->setGateway(new BankTransferPayment());
echo $checkout->processOrder(500000) . "\n";

echo "\n";

echo "OBSERVER\n";

$userService = new UserService();
$logger = new LoggerObserver();
$emailer = new EmailObserver();

$userService->attach($logger);
$userService->attach($emailer);

$userService->registerUser('budi@test.com');
$userService->loginUser('budi@test.com');
$userService->registerUser('ani@test.com');

echo "Logs:\n";
foreach ($logger->getLogs() as $log) {
    echo "  $log\n";
}

echo "Emails sent:\n";
foreach ($emailer->getSent() as $email) {
    echo "  $email\n";
}

echo "\n";

echo "REPOSITORY\n";

$repo = new InMemoryUserRepository();

$budi = new UserModel(0, 'Budi Santoso', 'budi@test.com');
$ani = new UserModel(0, 'Ani Wijaya', 'ani@test.com');

$repo->save($budi);
$repo->save($ani);

echo "All users:\n";
foreach ($repo->findAll() as $u) {
    echo "  {$u->getName()} ({$u->getEmail()})\n";
}

$found = $repo->findByEmail('budi@test.com');
echo "Found by email: {$found->getName()}\n";

echo "\n";

echo "ADAPTER\n";

$tmp = sys_get_temp_dir() . '/test.txt';

$local = new LocalFileStorage();
echo $local->write($tmp, "Hello World") . "\n";
echo "Local read: " . $local->read($tmp) . "\n";
$local->delete($tmp);

$cloud = new CloudStorageAPI();
$cloudAdapter = new CloudStorageAdapter($cloud, 'my-bucket');
echo $cloudAdapter->write('file.txt', 'data') . "\n";
echo "Cloud exists: " . ($cloudAdapter->exists('file.txt') ? 'yes' : 'no') . "\n";

echo "\n";

echo "DECORATOR\n";

$coffee = new SimpleCoffee();
echo "{$coffee->getDescription()}: Rp {$coffee->getCost()}\n";

$coffeeWithMilk = new MilkDecorator($coffee);
echo "{$coffeeWithMilk->getDescription()}: Rp {$coffeeWithMilk->getCost()}\n";

$coffeeWithMilkAndSugar = new SugarDecorator($coffeeWithMilk);
echo "{$coffeeWithMilkAndSugar->getDescription()}: Rp {$coffeeWithMilkAndSugar->getCost()}\n";

$fullCoffee = new WhippedCreamDecorator(new SugarDecorator(new MilkDecorator(new SimpleCoffee())));
echo "{$fullCoffee->getDescription()}: Rp {$fullCoffee->getCost()}\n";

echo "\nSelesai belajar design patterns!\n";
