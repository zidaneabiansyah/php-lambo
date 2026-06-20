<?php

// 09 - FILE I/O & SUPERGLOBALS
// Topik: Superglobals ($_SERVER, $_GET, $_POST,
//        $_SESSION, $_COOKIE, $_FILES, $_ENV),
//        File I/O (read, write, stream, lock),
//        Directory operations, CSV/INI parsing

require_once __DIR__ . '/superglobals.php';
require_once __DIR__ . '/files.php';
require_once __DIR__ . '/streams.php';

// ----- 1. SUPERGLOBALS -----

echo "SUPERGLOBALS\n";

$demo = new SuperglobalDemo();
$demo->demoServer();
$demo->demoGet(['page' => 'home', 'action' => 'list']);
$demo->demoPost(['username' => 'budi', 'password' => 'secret']);
$demo->demoRequest();
$demo->demoCookie();
$demo->demoEnv();
$demo->demoGlobals();

echo "\n";

// ----- 2. REQUEST CLASS (Wrapper) -----

echo "REQUEST WRAPPER\n";

$request = new Request([
    'query' => ['page' => '2', 'search' => 'php'],
    'body' => ['email' => 'budi@test.com', 'name' => 'Budi'],
    'server' => [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI' => '/users?page=2',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_AUTHORIZATION' => 'Bearer token123',
        'REMOTE_ADDR' => '192.168.1.100',
    ],
]);

echo "Method: " . $request->method() . "\n";
echo "URI: " . $request->uri() . "\n";
echo "Input page: " . $request->input('page') . "\n";
echo "Input email: " . $request->post('email') . "\n";
echo "Search: " . $request->query('search') . "\n";
echo "Is AJAX: " . ($request->isAjax() ? 'yes' : 'no') . "\n";
echo "Client IP: " . $request->ip() . "\n";
echo "Auth header: " . ($request->header('Authorization') ?? 'none') . "\n";

echo "\n";

// ----- 3. FILE I/O -----

echo "FILE I/O\n";

$fm = new FileManager();

// Write file
$bytes = $fm->write('test.txt', "Halo, ini file pertama!\nBaris kedua\n");
echo "Written: $bytes bytes\n";

// Append
$fm->append('test.txt', "Baris ketiga (append)\n");

// Read
$content = $fm->read('test.txt');
echo "Content:\n$content\n";

// File info
$info = $fm->info('test.txt');
echo "Info:\n";
foreach ($info as $key => $val) {
    echo "  $key: " . ($val ?? 'N/A') . "\n";

}

// Copy
$fm->copy('test.txt', 'backup.txt');
echo "Copied to backup.txt\n";

// Rename
$fm->rename('backup.txt', 'backup_2024.txt');
echo "Renamed backup\n";

echo "\n";

// ----- 4. DIRECTORY OPERATIONS -----

echo "DIRECTORY OPERATIONS\n";

$fm->makeDir('data/users');
$fm->makeDir('data/logs');
$fm->write('data/users/budi.txt', 'User: Budi');
$fm->write('data/users/ani.txt', 'User: Ani');
$fm->write('data/logs/app.log', '[INFO] Started');

$items = $fm->list('data');
echo "Items in data/:\n";
foreach ($items as $item) {
    $type = $item['type'];
    $name = $item['name'];
    $size = $item['size'];
    echo "  [$type] $name (" . ($type === 'file' ? "$size bytes" : 'dir') . ")\n";
}

echo "\n";

// ----- 5. RECURSIVE WALK -----

echo "RECURSIVE WALK\n";

foreach ($fm->walk('data') as $entry) {
    echo "  {$entry['path']} ({$entry['size']} bytes)\n";
}

echo "\n";

// ----- 6. CSV -----

echo "CSV HANDLER\n";

$csvData = [
    ['nama' => 'Budi', 'email' => 'budi@test.com', 'umur' => 25],
    ['nama' => 'Ani', 'email' => 'ani@test.com', 'umur' => 23],
    ['nama' => 'Citra', 'email' => 'citra@test.com', 'umur' => 28],
];

$csvPath = $fm->getBaseDir() . '/users.csv';
CsvHandler::write($csvPath, $csvData);
echo "CSV written: " . count($csvData) . " records\n";

$loaded = CsvHandler::read($csvPath);
echo "CSV loaded: " . count($loaded) . " records\n";
foreach ($loaded as $row) {
    echo "  {$row['nama']} - {$row['email']} ({$row['umur']})\n";
}

echo "\n";

// ----- 7. INI CONFIG -----

echo "INI CONFIG\n";

$config = new IniConfig();
$config->set('app.name', 'BelajarPHP');
$config->set('app.version', '1.0');
$config->set('app.debug', true);
$config->set('database.host', 'localhost');
$config->set('database.port', 3306);
$config->set('database.name', 'belajar_php');

$iniPath = $fm->getBaseDir() . '/config.ini';
$config->save($iniPath);

$loadedConfig = new IniConfig($iniPath);
echo "app.name: " . $loadedConfig->get('app.name') . "\n";
echo "database.host: " . $loadedConfig->get('database.host') . "\n";
echo "database.port: " . $loadedConfig->get('database.port') . "\n";

echo "\n";

// ----- 8. STREAMS -----

echo "STREAMS\n";

$streamDemo = new StreamDemo();
echo $streamDemo->phpMemory();
echo "Uppercase filter: " . $streamDemo->phpFilter("hello world\n");
$streamDemo->wrapperDemo();

registerCustomStream();
$customContent = file_get_contents('custom://test');
echo "Custom stream content: '$customContent'\n";

file_put_contents('custom://test', 'Data via custom stream');
$readBack = file_get_contents('custom://test');
echo "Read back: '$readBack'\n";

echo "\n";

// ----- 9. LOW LEVEL FILE -----

lowLevelFileDemo();
fileLockDemo();

echo "\n";

// ----- 10. CLEANUP -----

echo "CLEANUP\n";

$fm->delete('test.txt');
$fm->delete('backup_2024.txt');
$fm->removeDir('data');
echo "Temporary files cleaned\n";

echo "\nSelesai belajar file I/O & superglobals!\n";
