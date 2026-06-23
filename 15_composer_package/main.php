<?php

// 15 - COMPOSER & THIRD-PARTY PACKAGES
// Topik: Composer init, require, autoload PSR-4,
//        phpdotenv (.env), Carbon (datetime),
//        Guzzle (HTTP client), composer scripts

use App\Config;
use App\Helpers;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$baseDir = __DIR__;
$autoloadPath = $baseDir . '/vendor/autoload.php';

// Cek apakah vendor autoload ada
$composerInstalled = file_exists($autoloadPath);

if ($composerInstalled) {
    require_once $autoloadPath;
} else {
    echo "Composer belum diinstall. Jalankan: composer install\n";
    echo "Tapi kita bisa tetap pakai autoloader manual.\n\n";

    // Manual PSR-4 autoloader sebagai fallback
    spl_autoload_register(function ($class) use ($baseDir) {
        $prefix = 'App\\';
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . '/src/App/' . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    });
}

// ----- 1. APA ITU COMPOSER -----

echo "APA ITU COMPOSER\n";
echo "Composer adalah dependency manager untuk PHP.\n";
echo "Fitur utama:\n";
echo "  1. Mengelola library/package pihak ketiga\n";
echo "  2. PSR-4 autoloading otomatis\n";
echo "  3. Version constraints (semver)\n";
echo "  4. Script hooks (pre/post install, update)\n";
echo "  5. Autoload optimization\n\n";

// ----- 2. COMPOSER.JSON -----

echo "COMPOSER.JSON\n";
echo "File konfigurasi ada di: " . $baseDir . "/composer.json\n\n";
$composerContent = file_get_contents($baseDir . '/composer.json');
echo $composerContent . "\n";

echo "Key yang penting:\n";
echo "  require: Daftar package dan versi yang dibutuhkan\n";
echo "  autoload.psr-4: Mapping namespace ke direktori\n";
echo "  scripts: Command shortcuts (composer run start)\n";

echo "\n";

// ----- 3. COMPOSER AUTOLOAD -----

echo "COMPOSER AUTOLOAD (PSR-4)\n";

$config = new Config();
$helpers = new Helpers();

echo "Autoload berhasil!\n";
echo "  App\\\\Config ditemukan di: " . (new ReflectionClass(Config::class))->getFileName() . "\n";
echo "  App\\\\Helpers ditemukan di: " . (new ReflectionClass(Helpers::class))->getFileName() . "\n";

echo "\n";

// ----- 4. MENGGUNAKAN PACKAGE: vlucas/phpdotenv -----

echo ".ENV dengan PHP DOTENV\n";

if ($composerInstalled && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable($baseDir);
    $dotenv->load();
    echo ".env berhasil di-load!\n";
} else {
    // Fallback: load manual
    $envFile = $baseDir . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\' ');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
        echo ".env di-load manual (phpdotenv tidak terinstall)\n";
    } else {
        echo "File .env tidak ditemukan\n";
    }
}

echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'N/A') . "\n";
echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? 'N/A') . "\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'N/A') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'N/A') . "\n";

echo "\n";

// ----- 5. CONFIG CLASS (dengan env) -----

echo "CONFIG CLASS\n";

echo "App name: " . $config->get('app_name') . "\n";
echo "Environment: " . $config->get('app_env') . "\n";
echo "Debug mode: " . ($config->isDebug() ? 'ON' : 'OFF') . "\n";
echo "Is local: " . ($config->isLocal() ? 'yes' : 'no') . "\n";

echo "\n";

// ----- 6. HELPER FUNCTIONS -----

echo "HELPER FUNCTIONS\n";

echo "Rupiah: " . Helpers::formatRupiah(2500000) . "\n";
echo "Slug: " . Helpers::generateSlug('Belajar Composer di PHP!') . "\n";
echo "Truncate: " . Helpers::truncate('Ini adalah teks yang sangat panjang sekali untuk demo truncate function', 30) . "\n";
echo "Mask email: " . Helpers::maskEmail('budi.santoso@example.com') . "\n";
echo "Random: " . Helpers::randomString(8) . "\n";
echo "Time ago: " . Helpers::timeAgo(date('Y-m-d H:i:s', strtotime('-2 hours'))) . "\n";

echo "\n";

// ----- 7. MENGGUNAKAN CARBON (nesbot/carbon) -----

echo "CARBON (DateTime Library)\n";

if ($composerInstalled && class_exists('Carbon\Carbon')) {
    $now = Carbon::now();
    echo "Sekarang: " . $now->format('d M Y H:i:s') . "\n";

    $tomorrow = Carbon::tomorrow();
    echo "Besok: " . $tomorrow->format('d M Y') . "\n";

    $nextWeek = Carbon::now()->addWeek();
    echo "Minggu depan: " . $nextWeek->format('d M Y') . "\n";

    $diff = Carbon::now()->diffForHumans(Carbon::now()->subDays(3));
    echo "3 hari yang lalu: $diff\n";

    $age = Carbon::create(2000, 6, 15)->age;
    echo "Umur (lahir 15 Juni 2000): $age tahun\n";

    $formatted = Carbon::parse('2024-12-25')->isoFormat('dddd, D MMMM Y');
    echo "Christmas 2024: $formatted\n";

    echo "Carbon locale: " . Carbon::getLocale() . "\n";
} else {
    echo "Carbon tidak terinstall. Jalankan: composer require nesbot/carbon\n";
    echo "Fallback ke DateTime bawaan:\n";
    $now = new DateTime();
    echo "Sekarang: " . $now->format('d M Y H:i:s') . "\n";
    $age = (new DateTime('2000-06-15'))->diff(new DateTime())->y;
    echo "Umur (lahir 15 Juni 2000): $age tahun\n";
}

echo "\n";

// ----- 8. MENGGUNAKAN GUZZLE (HTTP Client) -----

echo "GUZZLE (HTTP Client)\n";

if ($composerInstalled && class_exists('GuzzleHttp\Client')) {
    $client = new Client([
        'base_uri' => 'https://jsonplaceholder.typicode.com',
        'timeout' => 10,
    ]);

    try {
        $response = $client->get('/posts/1');
        $post = json_decode($response->getBody(), true);

        echo "HTTP Status: " . $response->getStatusCode() . "\n";
        echo "Post title: " . ($post['title'] ?? 'N/A') . "\n";
        echo "Post body: " . Helpers::truncate($post['body'] ?? '', 80) . "\n";

        // POST request
        $newPost = $client->post('/posts', [
            'json' => [
                'title' => 'Belajar Guzzle',
                'body' => 'Mengirim data dengan Guzzle HTTP client',
                'userId' => 1,
            ]
        ]);

        $created = json_decode($newPost->getBody(), true);
        echo "Created post ID: " . ($created['id'] ?? 'N/A') . "\n";

    } catch (RequestException $e) {
        echo "HTTP Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Guzzle tidak terinstall. Jalankan: composer require guzzlehttp/guzzle\n";
    echo "Fallback ke file_get_contents:\n";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
        ]
    ]);

    $result = @file_get_contents('https://jsonplaceholder.typicode.com/posts/1', false, $context);
    if ($result !== false) {
        $post = json_decode($result, true);
        echo "Post title: " . ($post['title'] ?? 'N/A') . "\n";
    } else {
        echo "Tidak bisa connect (offline atau blocked)\n";
    }
}

echo "\n";

// ----- 9. COMPOSER COMMANDS -----

echo "COMPOSER COMMANDS\n";

$commands = [
    'composer init' => 'Membuat composer.json baru secara interaktif',
    'composer install' => 'Install semua package dari composer.lock (atau composer.json)',
    'composer update' => 'Update semua package ke versi terbaru sesuai constraint',
    'composer require <package>' => 'Install package baru dan tambahkan ke composer.json',
    'composer remove <package>' => 'Hapus package dari project',
    'composer dump-autoload' => 'Regenerasi autoload files',
    'composer show' => 'Lihat daftar package yang terinstall',
    'composer outdated' => 'Lihat package yang punya versi lebih baru',
    'composer audit' => 'Cek security vulnerabilities',
    'composer run <script>' => 'Jalankan script dari composer.json',
    'composer check-platform-reqs' => 'Cek PHP version requirements',
];

foreach ($commands as $cmd => $desc) {
    echo "  $cmd\n";
    echo "    $desc\n\n";
}

// ----- 10. VERSION CONSTRAINTS -----

echo "VERSION CONSTRAINTS\n";

$constraints = [
    '^1.0' => '>=1.0.0 dan <2.0.0 (compatible with 1.x)',
    '~1.2' => '>=1.2.0 dan <1.3.0 (approximately 1.2)',
    '1.4.*' => '>=1.4.0 dan <1.5.0 (wildcard)',
    '>=1.0' => 'Minimal versi 1.0',
    '1.0 - 2.0' => 'Range 1.0 sampai 2.0',
    'dev-main' => 'Development branch main',
    'all' => 'Versi berapapun (*)',
];

foreach ($constraints as $constraint => $meaning) {
    echo "  $constraint -> $meaning\n";
}

echo "\n";

// ----- 11. COMPOSER.LOCK & VENDOR -----

echo "COMPOSER.LOCK & VENDOR\n";

echo "  composer.lock: Mengunci versi tepat dari semua dependency\n";
echo "    Penting untuk reproducible builds di tim/deployment\n";
echo "    WAJIB di-commit ke git!\n\n";
echo "  vendor/: Direktori berisi semua package yang terinstall\n";
echo "    WAJIB di-.gitignore!\n";
echo "    Diregenerasi dengan: composer install\n\n";

echo "  Best practices:\n";
echo "    1. Commit composer.json dan composer.lock\n";
echo "    2. .gitignore vendor/\n";
echo "    3. require composer.json + composer.lock di production\n";
echo "    4. composer install --no-dev untuk production\n";
echo "    5. composer dump-autoload -o untuk optimized autoload\n";

echo "\nSelesai belajar composer & packages!\n";
