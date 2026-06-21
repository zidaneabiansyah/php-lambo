<?php

// 14 - NAMESPACE & AUTOLOAD
// Topik: Namespace, use/import, PSR-4 autoload,
//        custom autoloader, composer autoload

require_once __DIR__ . '/Autoloader.php';

// Register custom PSR-4 autoloader
$loader = new Autoloader();
$loader->addNamespace('App\\', __DIR__ . '/src/App/');
$loader->register();

// Function autoloading requires manual require (PSR-4 only covers classes)
require_once __DIR__ . '/src/App/Helpers/functions.php';

// ----- 1. BASIC NAMESPACE -----

echo "BASIC NAMESPACE\n";

// Fully qualified name
$user1 = new \App\Models\User(1, 'Budi Santoso', 'budi@test.com', 'admin');
$user2 = new \App\Models\User(2, 'Ani Wijaya', 'ani@test.com', 'user');
$user3 = new \App\Models\User(3, 'Citra Dewi', 'citra@test.com', 'user');

echo "User created: {$user1->getName()} ({$user1->getRole()})\n";
echo "Is admin: " . ($user1->isAdmin() ? 'yes' : 'no') . "\n";
echo "User 2 email: {$user2->getEmail()}\n";

$allUsers = \App\Models\User::all();
echo "Total users: " . count($allUsers) . "\n";

echo "\n";

// ----- 2. USE STATEMENT -----

echo "USE STATEMENT\n";

use App\Models\Post;
use App\Models\User as UserModel;
use App\Services\EmailService;
use App\Services\LoggerService;
use App\Utils\Database;

// Can use short class name after import
$post1 = new Post(1, 'Belajar Namespace PHP', 'Namespace memungkinkan kita mengorganisir kode dalam kelompok yang logis, mencegah konflik nama antar class yang berbeda.', 1);
$post2 = new Post(2, 'Autoloading dengan Composer', 'Composer menyediakan autoloading otomatis berdasarkan standar PSR-4, sehingga kita tidak perlu require manual setiap file.', 1);
$post3 = new Post(3, 'PSR Standards', 'PHP-FIG mendefinisikan standar coding seperti PSR-1, PSR-4, PSR-7, dan PSR-12 yang diikuti oleh framework modern.', 2);

echo "Posts:\n";
foreach (Post::all() as $p) {
    echo "  [{$p->getId()}] {$p->getTitle()}\n";
}

// Alias
$budi = UserModel::find(1);
echo "Alias UserModel: {$budi->getName()}\n";

echo "\n";

// ----- 3. MULTIPLE IMPORTS -----

echo "MULTIPLE NAMESPACE USAGE\n";

$email = new EmailService('mailgun', ['from' => 'belajar@example.com']);
$email->send('user@test.com', 'Selamat Datang', 'Terima kasih telah mendaftar!');
$email->send('admin@test.com', 'User Baru', 'Ada user baru yang mendaftar.');
echo "Total sent: {$email->getSentCount()}\n";

echo "\n";

// ----- 4. AUTOLOADER RESOLUTION -----

echo "AUTOLOADER RESOLUTION\n";

echo "Finding App\\Models\\User: " . $loader->findFile('App\\Models\\User') . "\n";
echo "Finding App\\Services\\EmailService: " . $loader->findFile('App\\Services\\EmailService') . "\n";

echo "\nLoaded classes from autoload:\n";
foreach ($loader->getLoadedClasses() as $class) {
    echo "  $class\n";
}

echo "\n";

// ----- 5. USE WITH FUNCTION & CONSTANT -----

echo "FUNCTION & CONSTANT\n";

// PHP 7.0+ juga bisa import function dan constant
// use function App\Helpers\formatRupiah;
// use const App\Helpers\VERSION;

// Simulasi
function formatRupiah(int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

echo "Harga: " . formatRupiah(2500000) . "\n";

echo "\n";

// ----- 6. GLOBAL VS NAMESPACE -----

echo "GLOBAL VS NAMESPACE\n";

// Global namespace class
$dt = new DateTime();
echo "Global DateTime: " . $dt->format('Y-m-d H:i:s') . "\n";

// Import dan gunakan function dari namespace
use function App\Helpers\now as nowHelper;
use function App\Helpers\formatRupiah as formatRupiahHelper;
use function App\Helpers\slugify;

echo "Helper now: " . nowHelper() . "\n";
echo "Rupiah: " . formatRupiahHelper(1500000) . "\n";
echo "Slug: " . slugify('Belajar PHP Namespace') . "\n";

echo "\n";

// ----- 7. COMPOSER AUTOLOAD (explanation) -----

echo "COMPOSER AUTOLOAD\n";

echo "Untuk menggunakan composer autoload:\n";
echo "  1. composer init\n";
echo "  2. Set autoload.psr-4 di composer.json\n";
echo "  3. composer dump-autoload\n";
echo "  4. require __DIR__ . '/vendor/autoload.php';\n";

echo "\nFile composer.json sudah dibuat:\n";
echo file_get_contents(__DIR__ . '/composer.json');

echo "\n";

// ----- 8. PRACTICAL DEMO -----

echo "PRACTICAL DEMO\n";

use App\Models\User as Member;

function printUserInfo(Member $user): void
{
    echo "  {$user->getName()} ({$user->getEmail()}) - {$user->getRole()}\n";
}

echo "All users:\n";
foreach (Member::all() as $u) {
    printUserInfo($u);
}

echo "\nUser 1 posts:\n";
foreach (Post::findByUser(1) as $p) {
    echo "  [{$p->getId()}] {$p->getTitle()}\n";
    echo "    {$p->getExcerpt(60)}\n";
}

echo "\n";

// ----- 9. AUTOLOAD UNREGISTER -----

$loader->unregister();

echo "Autoloader unregistered\n";
echo "\nSelesai belajar namespace & autoload!\n";
