<?php

// ============================================
// 02 - STRUKTUR DATA: ARRAY
// ============================================
// Topik: Array Indexed, Asosiatif, Multidimensi,
//        Array Functions, List/Destructuring,
//        Spread Operator, Array Unpacking
// ============================================

// ----- 1. ARRAY INDEXED -----

echo "=== ARRAY INDEXED ===\n";

$buah = ["apel", "mangga", "jeruk", "pisang"];
echo $buah[0] . "\n";  // apel
echo $buah[2] . "\n";  // jeruk

// Menambah elemen
$buah[] = "anggur";
array_push($buah, "melon");

// Menghapus elemen
unset($buah[1]);           // hapus index 1
$buah = array_values($buah);  // re-index

print_r($buah);

echo "Jumlah buah: " . count($buah) . "\n";

// Iterasi indexed array
foreach ($buah as $i => $b) {
    echo "$i: $b\n";
}

// ----- 2. ARRAY ASOSIATIF -----

echo "\n=== ARRAY ASOSIATIF ===\n";

$siswa = [
    "nama" => "Budi",
    "umur" => 25,
    "jurusan" => "Informatika",
    "ipk" => 3.85
];

echo "Nama: " . $siswa["nama"] . "\n";
echo "IPK: " . $siswa["ipk"] . "\n";

// Menambah/mengubah
$siswa["semester"] = 6;
$siswa["ipk"] = 3.90;

// Cek key exists
if (array_key_exists("alamat", $siswa)) {
    echo "Ada alamat\n";
} else {
    echo "Tidak ada alamat\n";
}

// Iterasi asosiatif
foreach ($siswa as $key => $value) {
    echo "$key: $value\n";
}

// ----- 3. ARRAY MULTIDIMENSI -----

echo "\n=== ARRAY MULTIDIMENSI ===\n";

$kelas = [
    ["nama" => "Budi", "nilai" => 85],
    ["nama" => "Ani", "nilai" => 92],
    ["nama" => "Citra", "nilai" => 78],
];

echo $kelas[0]["nama"] . ": " . $kelas[0]["nilai"] . "\n";

// Matriks 2x3
$matriks = [
    [1, 2, 3],
    [4, 5, 6],
];

echo "Matriks[1][2]: " . $matriks[1][2] . "\n";

// Iterasi multidimensi
foreach ($kelas as $murid) {
    echo "{$murid['nama']} mendapat nilai {$murid['nilai']}\n";
}

// ----- 4. ARRAY FUNCTIONS -----

echo "\n=== ARRAY FUNCTIONS ===\n";

$angka = [3, 1, 4, 1, 5, 9, 2, 6, 5];

// Sorting
sort($angka);
echo "Sorted: " . implode(", ", $angka) . "\n";

rsort($angka);
echo "Reverse: " . implode(", ", $angka) . "\n";

// Filter
$genap = array_filter($angka, fn($n) => $n % 2 == 0);
echo "Genap: " . implode(", ", $genap) . "\n";

// Map
$kuadrat = array_map(fn($n) => $n * $n, $angka);
echo "Kuadrat: " . implode(", ", $kuadrat) . "\n";

// Reduce
$jumlah = array_reduce($angka, fn($carry, $n) => $carry + $n, 0);
echo "Jumlah: $jumlah\n";

// Unique
$unik = array_unique($angka);
echo "Unik: " . implode(", ", $unik) . "\n";

// Merge
$arr1 = [1, 2, 3];
$arr2 = [4, 5, 6];
$gabung = array_merge($arr1, $arr2);
echo "Merge: " . implode(", ", $gabung) . "\n";

// Slice
$potong = array_slice($angka, 0, 3);
echo "Slice 3: " . implode(", ", $potong) . "\n";

// Search
$cari = array_search(5, $angka);
echo "Posisi angka 5: " . ($cari !== false ? $cari : "tidak ada") . "\n";

// In array
if (in_array(9, $angka)) {
    echo "Ada angka 9\n";
}

// Flip (tukar key => value)
$flip = array_flip($siswa);
print_r($flip);

// Keys & Values
echo "Keys: " . implode(", ", array_keys($siswa)) . "\n";
echo "Values: " . implode(", ", array_values($siswa)) . "\n";

// ----- 5. ARRAY DESTRUCTURING (PHP 7.1+) -----

echo "\n=== ARRAY DESTRUCTURING ===\n";

$koordinat = [10, 20];
[$x, $y] = $koordinat;
echo "x: $x, y: $y\n";

// Destructuring asosiatif
$profil = ["nama" => "Eko", "umur" => 30];
["nama" => $nama, "umur" => $umur] = $profil;
echo "$nama berumur $umur\n";

// Swap variable
$a = 5;
$b = 10;
[$a, $b] = [$b, $a];
echo "a: $a, b: $b\n";

// ----- 6. SPREAD OPERATOR (PHP 7.4+) -----

echo "\n=== SPREAD OPERATOR ===\n";

$arrA = [1, 2, 3];
$arrB = [0, ...$arrA, 4, 5];
echo "Spread: " . implode(", ", $arrB) . "\n";

function jumlahkanSemua(...$angka)
{
    return array_sum($angka);
}

echo "Jumlah: " . jumlahkanSemua(1, 2, 3, 4, 5) . "\n";

// ----- 7. ARRAY WALK -----

echo "\n=== ARRAY WALK ===\n";
$ganda = [1, 2, 3, 4, 5];
array_walk($ganda, fn(&$v) => $v *= 2);
echo "Walk: " . implode(", ", $ganda) . "\n";

// ----- 8. INSTANT ARRAY OPERATIONS -----

echo "\n=== INSTANT OPERATIONS ===\n";

// range
$rentang = range(1, 10);
echo "Range: " . implode(", ", $rentang) . "\n";

// fill
$terisi = array_fill(0, 5, "PHP");
print_r($terisi);

// chunk
$potongan = array_chunk(range(1, 10), 3);
print_r($potongan);

// column (untuk array multidimensi)
$nilaiSiswa = array_column($kelas, "nilai");
echo "Nilai semua: " . implode(", ", $nilaiSiswa) . "\n";

echo "\nSelesai belajar struktur data array!\n";
