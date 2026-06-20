<?php

// ============================================
// 04 - FUNGSI
// ============================================
// Topik: Deklarasi Fungsi, Parameter, Return,
//        Type Hints, Variadic, Named Arguments,
//        Anonymous Function, Arrow Function,
//        Closure, Callable, Generator, Rekursif
// ============================================

// ----- 1. FUNGSI DASAR -----

echo "=== FUNGSI DASAR ===\n";

function sapa()
{
    echo "Halo, selamat datang!\n";
}

sapa();

// ----- 2. PARAMETER & RETURN -----

echo "\n=== PARAMETER & RETURN ===\n";

function tambah(int $a, int $b): int
{
    return $a + $b;
}

echo "5 + 3 = " . tambah(5, 3) . "\n";

// Default parameter
function sapaOrang(string $nama = "Tamu"): string
{
    return "Halo, $nama!\n";
}

echo sapaOrang("Budi");
echo sapaOrang();

// Nullable parameter
function greet(?string $nama): void
{
    echo "Halo, " . ($nama ?? "Tamu") . "!\n";
}

greet(null);
greet("Ani");

// Union type (PHP 8.0+)
function formatNilai(int|float $nilai): int|string
{
    if ($nilai >= 90) return "A";
    if ($nilai >= 75) return "B";
    return (int) $nilai;
}

echo formatNilai(85) . "\n";
echo formatNilai(92.5) . "\n";

// Mixed type
function debug(mixed $data): void
{
    var_dump($data);
}

debug("test");
debug(123);

// ----- 3. PASS BY REFERENCE -----

echo "\n=== PASS BY REFERENCE ===\n";

function tambahLima(int &$nilai): void
{
    $nilai += 5;
}

$angka = 10;
tambahLima($angka);
echo "Setelah tambahLima: $angka\n";  // 15

// ----- 4. VARIADIC FUNCTION -----

echo "\n=== VARIADIC ===\n";

function jumlahSemua(int ...$angka): int
{
    return array_sum($angka);
}

echo jumlahSemua(1, 2, 3, 4, 5) . "\n";
echo jumlahSemua(10, 20) . "\n";

// Variadic dengan parameter biasa
function daftarBelanja(string $toko, string ...$items): void
{
    echo "Belanja di $toko:\n";
    foreach ($items as $item) {
        echo "  - $item\n";
    }
}

daftarBelanja("Supermarket", "Beras", "Gula", "Minyak");

// Spread argument
$angkaArr = [1, 2, 3, 4, 5];
echo "Max: " . max(...$angkaArr) . "\n";

// ----- 5. NAMED ARGUMENTS (PHP 8.0+) -----

echo "\n=== NAMED ARGUMENTS ===\n";

function buatKue(
    string $nama,
    string $rasa = "coklat",
    int $ukuran = 20,
    bool $glutenFree = false,
): string {
    return "$nama ($rasa, $ukuran cm" . ($glutenFree ? ", GF" : "") . ")";
}

echo buatKue("Kue Ulang Tahun", rasa: "vanila", glutenFree: true) . "\n";
echo buatKue(nama: "Donat", ukuran: 10) . "\n";

// ----- 6. STRICT TYPES & TYPE DECLARATIONS -----

echo "\n=== STRICT TYPES ===\n";
// declare(strict_types=1);  // Aktifkan di file terpisah

function kali(float $a, float $b): float
{
    return $a * $b;
}

echo kali(2.5, 3.0) . "\n";

// Void return
function logPesan(string $pesan): void
{
    echo "[LOG] $pesan\n";
}

logPesan("Aplikasi berjalan");

// Never return type (PHP 8.1+)
function hentikan(string $pesan): never
{
    die($pesan);
}
// hentikan("Error!"); // Uncomment untuk test

// ----- 7. ANONYMOUS FUNCTION (CLOSURE) -----

echo "\n=== ANONYMOUS FUNCTION ===\n";

$sapaAnon = function (string $nama): string {
    return "Halo, $nama!";
};

echo $sapaAnon("Budi") . "\n";

// Anonymous function sebagai callback
$angka2 = [1, 2, 3, 4, 5];
$kaliDua = array_map(function ($n) {
    return $n * 2;
}, $angka2);

echo "Kali dua: " . implode(", ", $kaliDua) . "\n";

// Closure dengan use
$pesan = "Halo";
$sapaDenganPesan = function (string $nama) use ($pesan): string {
    return "$pesan, $nama!";
};

echo $sapaDenganPesan("Budi") . "\n";

// Mengubah variable use dengan reference
$counter = 0;
$tambahCounter = function () use (&$counter): void {
    $counter++;
};

$tambahCounter();
$tambahCounter();
echo "Counter: $counter\n";

// ----- 8. ARROW FUNCTION (PHP 7.4+) -----

echo "\n=== ARROW FUNCTION ===\n";

$angka3 = [1, 2, 3, 4, 5, 6];

$genap = array_filter($angka3, fn($n) => $n % 2 == 0);
echo "Genap: " . implode(", ", $genap) . "\n";

$kuadratArr = array_map(fn($n) => $n * $n, $angka3);
echo "Kuadrat: " . implode(", ", $kuadratArr) . "\n";

// Arrow function dengan multiple expression (pakai helper)
$proses = array_map(fn($n) => $n > 3 ? $n * 2 : $n, $angka3);
echo "Proses: " . implode(", ", $proses) . "\n";

// Arrow function implicit capture (tidak perlu use)
$faktor = 3;
$kali = array_map(fn($n) => $n * $faktor, $angka3);
echo "Kali $faktor: " . implode(", ", $kali) . "\n";

// ----- 9. CALLABLE / CALLBACK -----

echo "\n=== CALLABLE ===\n";

function prosesArray(array $data, callable $callback): array
{
    $hasil = [];
    foreach ($data as $item) {
        $hasil[] = $callback($item);
    }
    return $hasil;
}

$data = [1, 2, 3, 4, 5];

// String function name
$hasil1 = prosesArray($data, 'abs');
echo "Abs: " . implode(", ", $hasil1) . "\n";

// Anonymous function
$hasil2 = prosesArray($data, function ($n) {
    return $n * 10;
});
echo "Kali 10: " . implode(", ", $hasil2) . "\n";

// Arrow function
$hasil3 = prosesArray($data, fn($n) => $n + 100);
echo "Tambah 100: " . implode(", ", $hasil3) . "\n";

// is_callable
if (is_callable('strtoupper')) {
    echo "strtoupper bisa dipanggil\n";
}

// call_user_func / call_user_func_array
echo call_user_func('strtoupper', 'halo') . "\n";
echo call_user_func_array('tambah', [10, 20]) . "\n";

// ----- 10. FUNCTION KOMPOSISI -----

echo "\n=== FUNCTION KOMPOSISI ===\n";

function pipe(mixed $nilai, callable ...$fungsi): mixed
{
    return array_reduce($fungsi, fn($carry, $fn) => $fn($carry), $nilai);
}

$hasilPipe = pipe(
    5,
    fn($n) => $n * 2,     // 10
    fn($n) => $n + 10,    // 20
    fn($n) => $n / 2,     // 10
);

echo "Pipe result: $hasilPipe\n";

// ----- 11. RECURSIVE FUNCTION -----

echo "\n=== RECURSIVE ===\n";

function faktorial(int $n): int
{
    if ($n <= 1) return 1;
    return $n * faktorial($n - 1);
}

echo "5! = " . faktorial(5) . "\n";

// Rekursif untuk nested array
$menu = [
    "name" => "Menu",
    "children" => [
        ["name" => "File", "children" => [
            ["name" => "New"],
            ["name" => "Open"],
            ["name" => "Save"],
        ]],
        ["name" => "Edit", "children" => [
            ["name" => "Copy"],
            ["name" => "Paste"],
        ]],
    ],
];

function printMenu(array $menu, int $depth = 0): void
{
    echo str_repeat("  ", $depth) . "- {$menu['name']}\n";
    if (isset($menu['children'])) {
        foreach ($menu['children'] as $child) {
            printMenu($child, $depth + 1);
        }
    }
}

printMenu($menu);

// ----- 12. GENERATOR (yield) -----

echo "\n=== GENERATOR ===\n";

function buatRange(int $dari, int $ke): Generator
{
    for ($i = $dari; $i <= $ke; $i++) {
        yield $i;
    }
}

foreach (buatRange(1, 5) as $angka) {
    echo "$angka ";
}
echo "\n";

// Generator dengan key-value
function daftarBuah(): Generator
{
    yield "a" => "Apel";
    yield "m" => "Mangga";
    yield "j" => "Jeruk";
}

foreach (daftarBuah() as $kode => $buah) {
    echo "$kode: $buah\n";
}

// Generator untuk file besar (read line by line)
function bacaFileBaris(string $file): Generator
{
    $handle = fopen($file, "r");
    while (($baris = fgets($handle)) !== false) {
        yield rtrim($baris, "\n");
    }
    fclose($handle);
}

// foreach (bacaFileBaris("file.txt") as $baris) {
//     echo $baris . "\n";
// }

// ----- 13. FIRST-CLASS CALLABLE (PHP 8.1+) -----

echo "\n=== FIRST-CLASS CALLABLE ===\n";

$strToUpper = strtoupper(...);
echo $strToUpper("hello") . "\n";

$filterGenap = array_filter(...);
$arrGenap = $filterGenap([1, 2, 3, 4, 5, 6], fn($n) => $n % 2 == 0);
echo "Genap: " . implode(", ", $arrGenap) . "\n";

// ----- 14. FUNCTION EXISTENCE -----

echo "\n=== FUNCTION EXISTENCE ===\n";

if (function_exists("tambah")) {
    echo "Fungsi tambah tersedia\n";
}

// Dapatkan daftar semua function yang terdefinisi
$allFuncs = get_defined_functions();
echo "Total user functions: " . count($allFuncs["user"]) . "\n";

echo "\nSelesai belajar fungsi di PHP!\n";
