<?php

// ============================================
// 03 - STRING & MANIPULASI
// ============================================
// Topik: String Functions, Heredoc/Nowdoc,
//        Regex, Multibyte String, Encoding,
//        Formatting, Parsing
// ============================================

// ----- 1. DASAR STRING -----

echo "=== DASAR STRING ===\n";

$nama = "Budi Santoso";
echo "Panjang: " . strlen($nama) . "\n";
echo "Karakter ke-3: " . $nama[2] . "\n";  // d (0-indexed)

// String access by character (PHP 7.4+)
echo "Karakter pertama: " . $nama[0] . "\n";

// ----- 2. MANIPULASI KASUS -----

echo "\n=== MANIPULASI KASUS ===\n";

$teks = "Halo Dunia PHP";

echo strtolower($teks) . "\n";  // halo dunia php
echo strtoupper($teks) . "\n";  // HALO DUNIA PHP
echo ucfirst("halo dunia") . "\n";   // Halo dunia
echo ucwords("halo dunia php") . "\n";  // Halo Dunia Php
echo lcfirst("Halo Dunia") . "\n";   // halo Dunia

// ----- 3. SUBSTRING & PEMOTONGAN -----

echo "\n=== SUBSTRING ===\n";

$kalimat = "Belajar PHP itu menyenangkan";

echo substr($kalimat, 0, 7) . "\n";       // Belajar
echo substr($kalimat, 8, 3) . "\n";       // PHP
echo substr($kalimat, -5) . "\n";         // ngkan (5 dari belakang)
echo substr($kalimat, -12, 5) . "\n";     // nyena

// ----- 4. PENCARIAN -----

echo "\n=== PENCARIAN ===\n";

$teksCari = "Halo, selamat datang di belajar PHP";

echo "Posisi 'selamat': " . strpos($teksCari, "selamat") . "\n";
echo "Posisi 'PHP': " . strpos($teksCari, "PHP") . "\n";

// strpos case-insensitive
echo "Posisi 'php' (case-insensitive): " . stripos($teksCari, "php") . "\n";

// str_contains (PHP 8.0+)
echo "Mengandung 'belajar': " . (str_contains($teksCari, "belajar") ? "ya" : "tidak") . "\n";

// str_starts_with / str_ends_with (PHP 8.0+)
echo "Mulai dengan 'Halo': " . (str_starts_with($teksCari, "Halo") ? "ya" : "tidak") . "\n";
echo "Akhir dengan 'PHP': " . (str_ends_with($teksCari, "PHP") ? "ya" : "tidak") . "\n";

// ----- 5. PENGGANTIAN -----

echo "\n=== PENGGANTIAN ===\n";

$asli = "Apel, Mangga, Apel, Jeruk";

// str_replace
echo str_replace("Apel", "Anggur", $asli) . "\n";

// Replace dengan array
$cari = ["Apel", "Mangga", "Jeruk"];
$ganti = ["Anggur", "Pisang", "Semangka"];
echo str_replace($cari, $ganti, $asli) . "\n";

// substr_replace
echo substr_replace("Hello World", "PHP", 6) . "\n";  // Hello PHP

// strtr (translation)
echo strtr("Halo Dunia", ["Halo" => "Selamat", "Dunia" => "Siang"]) . "\n";

// ----- 6. PEMISAHAN & PENGGABUNGAN -----

echo "\n=== PEMISAHAN & PENGGABUNGAN ===\n";

$csv = "apel,mangga,jeruk,pisang";
$arrBuah = explode(",", $csv);
print_r($arrBuah);

$arrBaru = ["satu", "dua", "tiga"];
echo implode(" - ", $arrBaru) . "\n";

// str_split
print_r(str_split("PHP", 2));

// chunk_split
echo chunk_split("HelloWorld", 3, "-") . "\n";  // Hel-loW-orld-

// ----- 7. PEMBERSIHAN STRING -----

echo "\n=== PEMBERSIHAN ===\n";

$kotor = "   Halo Dunia   \n\t";
echo "'" . trim($kotor) . "'\n";     // 'Halo Dunia'
echo "'" . ltrim($kotor) . "'\n";    // 'Halo Dunia   '
echo "'" . rtrim($kotor) . "'\n";    // '   Halo Dunia'

// Menghapus tag HTML
$html = "<p>Halo <b>Dunia</b></p>";
echo strip_tags($html) . "\n";        // Halo Dunia

// Menambahkan backslash
$sql = "O'Brien";
echo addslashes($sql) . "\n";         // O\'Brien
echo stripslashes("O\'Brien") . "\n"; // O'Brien

// htmlspecialchars (untuk XSS prevention)
$userInput = "<script>alert('xss')</script>";
echo htmlspecialchars($userInput) . "\n";
echo htmlentities($userInput) . "\n";

// ----- 8. FORMATTING STRING -----

echo "\n=== FORMATTING ===\n";

$nama = "Budi";
$umur = 25;
$ipk = 3.857;

// printf / sprintf
printf("Nama: %s, Umur: %d, IPK: %.2f\n", $nama, $umur, $ipk);

$formatted = sprintf("Nama: %s, Umur: %d, IPK: %.2f", $nama, $umur, $ipk);
echo $formatted . "\n";

// str_pad
echo str_pad("PHP", 10, "-", STR_PAD_BOTH) . "\n";  // ---PHP----
echo str_pad("PHP", 10, "*", STR_PAD_LEFT) . "\n";   // *******PHP

// str_repeat
echo str_repeat("=", 20) . "\n";

// number_format
$harga = 2500000;
echo "Rp " . number_format($harga, 0, ",", ".") . "\n";

// ----- 9. HEREDOC & NOWDOC -----

echo "\n=== HEREDOC & NOWDOC ===\n";

$nama = "Budi";

// Heredoc - interpolasi
$html = <<<HTML
<div>
    <h1>Selamat datang, $nama!</h1>
    <p>Ini adalah template heredoc</p>
</div>
HTML;

echo $html;

// Nowdoc - tanpa interpolasi (pakai 'Teks')
$teksLiteral = <<<'TEKS'
Ini adalah nowdoc, $nama TIDAK akan di-interpolasi
Bisa multi line tanpa parsing variable
TEKS;

echo $teksLiteral;

// ----- 10. MULTIBYTE STRING -----

echo "\n=== MULTIBYTE STRING ===\n";

$indo = "Bahasa Indonesia dengan é, ñ, ü";

echo "strlen: " . strlen($indo) . "\n";           // byte length
echo "mb_strlen: " . mb_strlen($indo) . "\n";     // character length

echo "mb_substr: " . mb_substr($indo, 0, 6) . "\n";

echo "mb_strpos: " . mb_strpos($indo, "Indonesia") . "\n";

echo mb_strtoupper($indo) . "\n";

// ----- 11. ENCODING & HASH -----

echo "\n=== ENCODING & HASH ===\n";

$data = "Rahasia123";

echo "Base64 encode: " . base64_encode($data) . "\n";
echo "Base64 decode: " . base64_decode(base64_encode($data)) . "\n";

echo "MD5: " . md5($data) . "\n";
echo "SHA1: " . sha1($data) . "\n";
echo "SHA256: " . hash("sha256", $data) . "\n";

// url encode/decode
$url = "nama= Budi & umur=25";
echo "URL encode: " . urlencode($url) . "\n";
echo "URL decode: " . urldecode(urlencode($url)) . "\n";

// http_build_query
$params = ["nama" => "Budi", "umur" => 25, "hobi" => ["coding", "gaming"]];
echo http_build_query($params) . "\n";

// parse_url
$urlParsed = "https://user:pass@example.com:8080/path?q=php#section";
print_r(parse_url($urlParsed));

// parse_str
$query = "nama=Budi&umur=25";
parse_str($query, $hasil);
print_r($hasil);

// ----- 12. REGEX (PCRE) -----

echo "\n=== REGEX ===\n";

$teks = "Email: budi@example.com, admin@test.co.id";

// preg_match
preg_match("/[\w\.-]+@[\w\.-]+\.\w+/", $teks, $match);
echo "Email pertama: " . $match[0] . "\n";

// preg_match_all
preg_match_all("/[\w\.-]+@[\w\.-]+\.\w+/", $teks, $matches);
print_r($matches[0]);

// preg_replace
$sensor = preg_replace("/\d{4,}/", "****", "Nomor: 123456789");
echo $sensor . "\n";

// preg_split
$parts = preg_split("/[\s,]+/", "apel mangga,jeruk  pisang");
print_r($parts);

// preg_grep
$arr = ["abc", "def", "abc123", "xyz"];
$hasilGrep = preg_grep("/^abc/", $arr);
print_r($hasilGrep);

// ----- 13. STRING COMPARISON -----

echo "\n=== STRING COMPARISON ===\n";

$a = "apel";
$b = "Apel";

echo strcmp($a, $b) . "\n";       // > 0 (case-sensitive)
echo strcasecmp($a, $b) . "\n";   // 0 (case-insensitive)

// strnatcmp (natural order)
$files = ["file2", "file10", "file1"];
natsort($files);
echo "Natural sort: " . implode(", ", $files) . "\n";

// similar_text
$kata1 = "belajar";
$kata2 = "belanja";
similar_text($kata1, $kata2, $persen);
echo "Similarity: $persen%\n";

// levenshtein
echo "Levenshtein distance: " . levenshtein("sitting", "kitten") . "\n";

// ----- 14. STRING KE ARRAY & SEBALIKNYA -----

echo "\n=== KONVERSI ===\n";

// String <-> Array
$str = "a,b,c,d";
print_r(str_getcsv($str));  // parse CSV string

// wordwrap
$panjang = "Belajar PHP itu menyenangkan dan menantang";
echo wordwrap($panjang, 10, "\n") . "\n";

// nl2br (untuk HTML)
$multiLine = "Baris 1\nBaris 2\nBaris 3";
echo nl2br($multiLine) . "\n";

// str_rot13
echo str_rot13("Hello World") . "\n";  // Uryyb Jbeyq

// soundex / metaphone (untuk pencarian fonetik)
echo "Soundex: " . soundex("Smith") . " = " . soundex("Smyth") . "\n";

echo "\nSelesai belajar string & manipulasi!\n";
