<?php

// ============================================
// 01 - DASAR PHP
// ============================================
// Topik: Variabel, Tipe Data, If/Else, Switch,
//        Match Expression, Loops, Operator
// ============================================

// ----- 1. OUTPUT & KOMENTAR -----

// Ini komentar satu baris

/*
 * Ini komentar
 * multi baris
 */

echo "Hello, PHP!\n";
print "Ini pakai print\n";

// ----- 2. VARIABEL & TIPE DATA -----

$nama = "Budi";          // string
$umur = 25;              // integer
$tinggi = 170.5;         // float (double)
$menikah = false;        // boolean
$hobi = null;            // null

var_dump($nama, $umur, $tinggi, $menikah, $hobi);

// Konstanta
define("APP_NAME", "BelajarPHP");
const VERSION = "1.0.0";

echo APP_NAME . " v" . VERSION . "\n";

// Type juggling & casting
$angkaString = "100";
$angka = (int) $angkaString;
echo $angka . "\n";

// ----- 3. STRING -----

$depan = "Budi";
$belakang = "Santoso";
$namaLengkap = "$depan $belakang";     // interpolasi
$namaLengkap2 = $depan . " " . $belakang;  // concatenation

echo "$namaLengkap\n";

// Heredoc
$teksPanjang = <<<Teks
Ini adalah teks panjang
menggunakan heredoc syntax
bisa multiple line
Teks;

echo $teksPanjang;

// ----- 4. OPERATOR -----

$a = 10;
$b = 3;

echo "Penjumlahan: " . ($a + $b) . "\n";
echo "Pengurangan: " . ($a - $b) . "\n";
echo "Perkalian: " . ($a * $b) . "\n";
echo "Pembagian: " . ($a / $b) . "\n";
echo "Modulus: " . ($a % $b) . "\n";
echo "Pangkat: " . ($a ** $b) . "\n";

// Increment/decrement
$counter = 0;
$counter++;
echo "Counter: $counter\n";

// Operator perbandingan
var_dump($a == $b);     // false
var_dump($a != $b);     // true
var_dump($a === "10");  // false (strict)
var_dump($a == "10");   // true (loose)

// Operator logika
$benar = true;
$salah = false;
var_dump($benar && $salah);  // false
var_dump($benar || $salah);  // true
var_dump(!$benar);           // false

// Null coalescing
$username = $_GET["user"] ?? "tamu";
echo "Username: $username\n";

// ----- 5. IF / ELSE / ELSEIF -----

$nilai = 85;

if ($nilai >= 90) {
    echo "Grade A\n";
} elseif ($nilai >= 75) {
    echo "Grade B\n";
} elseif ($nilai >= 60) {
    echo "Grade C\n";
} else {
    echo "Grade D\n";
}

// Ternary
$status = $nilai >= 60 ? "Lulus" : "Tidak Lulus";
echo "Status: $status\n";

// Null coalescing assignment
$data ??= "default";
echo "Data: $data\n";

// ----- 6. SWITCH -----

$hari = "senin";

switch ($hari) {
    case "senin":
        echo "Hari kerja, semangat!\n";
        break;
    case "sabtu":
    case "minggu":
        echo "Akhir pekan, santai!\n";
        break;
    default:
        echo "Hari biasa\n";
}

// ----- 7. MATCH EXPRESSION (PHP 8.0+) -----

$bulan = 2;

$namaBulan = match ($bulan) {
    1 => "Januari",
    2 => "Februari",
    3 => "Maret",
    4 => "April",
    5 => "Mei",
    6 => "Juni",
    7 => "Juli",
    8 => "Agustus",
    9 => "September",
    10 => "Oktober",
    11 => "November",
    12 => "Desember",
    default => "Bulan tidak valid"
};

echo "Bulan: $namaBulan\n";

// Match dengan kondisi
$nilaiHuruf = match (true) {
    $nilai >= 90 => 'A',
    $nilai >= 75 => 'B',
    $nilai >= 60 => 'C',
    default => 'D'
};

echo "Nilai huruf: $nilaiHuruf\n";

// ----- 8. LOOP -----

echo "\n--- FOR LOOP ---\n";
for ($i = 1; $i <= 5; $i++) {
    echo "Perulangan ke-$i\n";
}

echo "\n--- WHILE LOOP ---\n";
$i = 1;
while ($i <= 5) {
    echo "While ke-$i\n";
    $i++;
}

echo "\n--- DO WHILE ---\n";
$j = 1;
do {
    echo "Do while ke-$j\n";
    $j++;
} while ($j <= 5);

echo "\n--- FOREACH ---\n";
$buah = ["apel", "mangga", "jeruk", "pisang"];

foreach ($buah as $index => $item) {
    echo "$index: $item\n";
}

// Break & Continue
echo "\n--- BREAK & CONTINUE ---\n";
for ($i = 1; $i <= 10; $i++) {
    if ($i % 3 == 0) {
        continue;  // skip kelipatan 3
    }
    if ($i > 7) {
        break;     // stop di 7
    }
    echo "$i ";
}
echo "\n";

// ----- 9. INCLUDE / REQUIRE -----

// Biasanya dipakai untuk memisahkan file konfigurasi
// include "config.php";
// require_once "config.php";

echo "\nSelesai belajar PHP dasar!\n";
