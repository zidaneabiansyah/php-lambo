<?php

// ============================================
// 05 - OBJECT ORIENTED PROGRAMMING (OOP)
// ============================================
// Topik: Class, Object, Constructor, Property,
//        Method, Inheritance, Visibility,
//        Abstract, Interface, Trait, Static,
//        Final, Enum, Namespace, Type Variance
// ============================================

// ----- 1. CLASS & OBJECT -----

echo "=== CLASS & OBJECT ===\n";

class Mahasiswa
{
    // Property dengan type declaration (PHP 7.4+)
    public string $nama;
    public int $umur;
    public string $jurusan;
    private string $nim;
    protected array $nilai = [];

    // Constructor (promotion property - PHP 8.0+)
    public function __construct(
        string $nama,
        int $umur,
        string $jurusan,
        string $nim,
    ) {
        $this->nama = $nama;
        $this->umur = $umur;
        $this->jurusan = $jurusan;
        $this->nim = $nim;
    }

    // Method
    public function sapa(): string
    {
        return "Halo, saya {$this->nama} dari {$this->jurusan}";
    }

    public function getNim(): string
    {
        return $this->nim;
    }

    public function tambahNilai(int $nilai): void
    {
        $this->nilai[] = $nilai;
    }

    public function getRataNilai(): float
    {
        if (empty($this->nilai)) return 0;
        return array_sum($this->nilai) / count($this->nilai);
    }
}

$budi = new Mahasiswa("Budi", 20, "Informatika", "12345");
echo $budi->sapa() . "\n";
echo "NIM: " . $budi->getNim() . "\n";

$budi->tambahNilai(85);
$budi->tambahNilai(90);
$budi->tambahNilai(78);
echo "Rata-rata: " . $budi->getRataNilai() . "\n";

// Constructor property promotion (short syntax)
class Produk
{
    // Property promotion langsung dari constructor
    public function __construct(
        private string $nama,
        private float $harga,
        private int $stok = 0,
    ) {}

    public function getInfo(): string
    {
        return "{$this->nama} - Rp" . number_format($this->harga, 0, ",", ".")
            . " (Stok: {$this->stok})";
    }
}

$produk = new Produk("Laptop", 15000000, 5);
echo $produk->getInfo() . "\n";

// Readonly property (PHP 8.1+)
class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public string $displayName,
    ) {}

    // public function gantiId(string $id): void
    // {
    //     $this->id = $id; // ERROR: readonly!
    // }
}

$user = new User("U-001", "budi123", "Budi Santoso");
// $user->id = "U-002"; // ERROR: readonly!
echo "User: {$user->username} ({$user->displayName})\n";

// ----- 2. VISIBILITY -----

echo "\n=== VISIBILITY ===\n";

class BankAccount
{
    public string $pemilik;
    protected string $nomorRekening;
    private float $saldo = 0;

    public function __construct(string $pemilik, string $nomorRekening)
    {
        $this->pemilik = $pemilik;
        $this->nomorRekening = $nomorRekening;
    }

    public function setor(float $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException("Jumlah harus positif");
        }
        $this->saldo += $jumlah;
    }

    public function tarik(float $jumlah): bool
    {
        if ($jumlah > $this->saldo) {
            return false;
        }
        $this->saldo -= $jumlah;
        return true;
    }

    public function getSaldo(): float
    {
        return $this->saldo;
    }

    protected function getNomorRekening(): string
    {
        return $this->nomorRekening;
    }
}

$akun = new BankAccount("Budi", "123-456-789");
$akun->setor(1000000);
$akun->tarik(250000);
echo "Saldo {$akun->pemilik}: Rp" . number_format($akun->getSaldo(), 0, ",", ".") . "\n";

// ----- 3. INHERITANCE -----

echo "\n=== INHERITANCE ===\n";

class Hewan
{
    public function __construct(
        protected string $nama,
        protected int $umur,
    ) {}

    public function bersuara(): string
    {
        return "...";
    }

    public function getInfo(): string
    {
        return "{$this->nama}, {$this->umur} tahun";
    }
}

class Kucing extends Hewan
{
    private string $ras;

    public function __construct(string $nama, int $umur, string $ras)
    {
        parent::__construct($nama, $umur);
        $this->ras = $ras;
    }

    public function bersuara(): string
    {
        return "Meow!";
    }

    public function getInfo(): string
    {
        return parent::getInfo() . ", {$this->ras}";
    }
}

class Anjing extends Hewan
{
    public function bersuara(): string
    {
        return "Woof!";
    }
}

$kucing = new Kucing("Tom", 3, "Persia");
$anjing = new Anjing("Spike", 2);

echo "{$kucing->getInfo()}: {$kucing->bersuara()}\n";
echo "{$anjing->getInfo()}: {$anjing->bersuara()}\n";

// ----- 4. ABSTRACT CLASS -----

echo "\n=== ABSTRACT CLASS ===\n";

abstract class Bentuk
{
    protected string $warna;

    public function __construct(string $warna)
    {
        $this->warna = $warna;
    }

    abstract public function hitungLuas(): float;

    public function getWarna(): string
    {
        return $this->warna;
    }
}

class Lingkaran extends Bentuk
{
    public function __construct(
        string $warna,
        private float $jariJari,
    ) {
        parent::__construct($warna);
    }

    public function hitungLuas(): float
    {
        return pi() * $this->jariJari * $this->jariJari;
    }
}

class Persegi extends Bentuk
{
    public function __construct(
        string $warna,
        private float $sisi,
    ) {
        parent::__construct($warna);
    }

    public function hitungLuas(): float
    {
        return $this->sisi * $this->sisi;
    }
}

$lingkaran = new Lingkaran("Merah", 7);
$persegi = new Persegi("Biru", 5);

echo "Lingkaran {$lingkaran->getWarna()}: " . round($lingkaran->hitungLuas(), 2) . "\n";
echo "Persegi {$persegi->getWarna()}: " . $persegi->hitungLuas() . "\n";

// ----- 5. INTERFACE -----

echo "\n=== INTERFACE ===\n";

interface Logger
{
    public function log(string $pesan): void;
    public function getLogs(): array;
}

interface Notifiable
{
    public function kirimNotifikasi(string $pesan): bool;
}

class FileLogger implements Logger, Notifiable
{
    private array $logs = [];

    public function log(string $pesan): void
    {
        $this->logs[] = "[" . date("Y-m-d H:i:s") . "] $pesan";
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function kirimNotifikasi(string $pesan): bool
    {
        $this->log("[NOTIF] $pesan");
        return true;
    }
}

$logger = new FileLogger();
$logger->log("Aplikasi mulai");
$logger->log("User login");
$logger->kirimNotifikasi("Selamat datang!");

foreach ($logger->getLogs() as $log) {
    echo "$log\n";
}

// Interface segregation
interface Readable
{
    public function baca(): string;
}

interface Writable
{
    public function tulis(string $data): void;
}

class FileDokumen implements Readable, Writable
{
    private string $konten = "";

    public function baca(): string
    {
        return $this->konten;
    }

    public function tulis(string $data): void
    {
        $this->konten = $data;
    }
}

// ----- 6. TRAIT -----

echo "\n=== TRAIT ===\n";

trait Timestampable
{
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function initTimestamps(): void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt->format("Y-m-d H:i:s");
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt->format("Y-m-d H:i:s");
    }
}

trait JsonSerializable
{
    public function toJson(): string
    {
        return json_encode(get_object_vars($this), JSON_PRETTY_PRINT);
    }
}

class Artikel
{
    use Timestampable, JsonSerializable;

    public function __construct(
        private string $judul,
        private string $konten,
    ) {
        $this->initTimestamps();
    }

    public function getJudul(): string
    {
        return $this->judul;
    }
}

$artikel = new Artikel("Belajar PHP OOP", "Ini adalah konten...");
echo $artikel->toJson() . "\n";

// Trait dengan abstract method
trait Validatable
{
    abstract public function getValidationRules(): array;

    public function validate(): bool
    {
        foreach ($this->getValidationRules() as $field => $rules) {
            echo "Validasi $field...\n";
        }
        return true;
    }
}

class FormSignup
{
    use Validatable;

    public function __construct(
        private string $email,
        private string $password,
    ) {}

    public function getValidationRules(): array
    {
        return [
            "email" => ["required", "email"],
            "password" => ["required", "min:8"],
        ];
    }
}

$form = new FormSignup("budi@test.com", "rahasia123");
$form->validate();

// Trait conflict resolution
trait A
{
    public function doSomething(): void
    {
        echo "Trait A\n";
    }
}

trait B
{
    public function doSomething(): void
    {
        echo "Trait B\n";
    }
}

class MyClass
{
    use A, B {
        B::doSomething insteadof A;
        A::doSomething as doA;
    }
}

$mc = new MyClass();
$mc->doSomething();  // Trait B
$mc->doA();          // Trait A

// ----- 7. STATIC MEMBER -----

echo "\n=== STATIC ===\n";

class Database
{
    private static ?Database $instance = null;
    private static int $totalKoneksi = 0;
    private string $nama;

    private function __construct(string $nama)
    {
        $this->nama = $nama;
        self::$totalKoneksi++;
    }

    public static function getInstance(string $nama = "default"): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database($nama);
        }
        return self::$instance;
    }

    public static function getTotalKoneksi(): int
    {
        return self::$totalKoneksi;
    }

    public function query(string $sql): string
    {
        return "Hasil query dari {$this->nama}: $sql";
    }
}

$db1 = Database::getInstance();
$db2 = Database::getInstance();

echo $db1->query("SELECT * FROM users") . "\n";
echo "Total koneksi: " . Database::getTotalKoneksi() . "\n";
echo "Sama?: " . ($db1 === $db2 ? "Ya, singleton" : "Tidak") . "\n";

// Static property & method
class Config
{
    private static array $data = [];

    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$data[$key] ?? $default;
    }

    public static function all(): array
    {
        return self::$data;
    }
}

Config::set("app_name", "BelajarPHP");
Config::set("version", "1.0");
Config::set("debug", true);

echo "App: " . Config::get("app_name") . "\n";
echo "Debug: " . (Config::get("debug") ? "true" : "false") . "\n";

// ----- 8. CONSTANT & FINAL -----

echo "\n=== CONSTANT & FINAL ===\n";

class StatusPembayaran
{
    public const PENDING = "pending";
    public const SUCCESS = "success";
    public const FAILED = "failed";
    public const REFUND = "refund";

    private const TAX_RATE = 0.11;  // 11% PPn

    public static function isValid(string $status): bool
    {
        $valid = [self::PENDING, self::SUCCESS, self::FAILED, self::REFUND];
        return in_array($status, $valid);
    }

    public static function hitungPajak(float $jumlah): float
    {
        return $jumlah * self::TAX_RATE;
    }
}

echo StatusPembayaran::PENDING . "\n";
echo "Pajak: " . StatusPembayaran::hitungPajak(100000) . "\n";

// Final class (tidak bisa di-extends)
final class MathUtils
{
    public static function kuadrat(float $n): float
    {
        return $n * $n;
    }
}

echo MathUtils::kuadrat(5) . "\n";

// class MathExtended extends MathUtils {} // ERROR: final class

// Final method
class BaseClass
{
    final public function不能被覆盖(): void
    {
        echo "Method final tidak bisa di-override\n";
    }
}

// ----- 9. LATE STATIC BINDING -----

echo "\n=== LATE STATIC BINDING ===\n";

class Karyawan
{
    protected static string $role = "Karyawan";

    public static function getRole(): string
    {
        // self::$role -> static binding (early)
        return self::$role;
    }

    public static function getRoleStatic(): string
    {
        // static::$role -> late static binding
        return static::$role;
    }

    public static function buat(): static
    {
        return new static();
    }
}

class Manager extends Karyawan
{
    protected static string $role = "Manager";
}

class Direktur extends Karyawan
{
    protected static string $role = "Direktur";
}

echo "Karyawan role (self): " . Karyawan::getRole() . "\n";
echo "Manager role (self): " . Manager::getRole() . "\n";       // "Karyawan" (salah!)
echo "Manager role (static): " . Manager::getRoleStatic() . "\n"; // "Manager" (benar)

$manager = Manager::buat();
echo "Class: " . $manager::class . "\n";

// ----- 10. MAGIC METHODS OVERVIEW -----

echo "\n=== MAGIC METHODS OVERVIEW ===\n";

// Detail lengkap di module 07_magic_methods
class UserModel
{
    public function __construct(
        private array $data = [],
    ) {}

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function __toString(): string
    {
        return json_encode($this->data);
    }
}

$userModel = new UserModel(["nama" => "Budi"]);
echo $userModel->nama . "\n";
$userModel->email = "budi@test.com";
echo $userModel;

// ----- 11. ENUM (PHP 8.1+) -----

echo "\n=== ENUM ===\n";

enum StatusOrder: string
{
    case PENDING = "pending";
    case PROCESSING = "processing";
    case SHIPPED = "shipped";
    case DELIVERED = "delivered";
    case CANCELLED = "cancelled";

    public function label(): string
    {
        return match ($this) {
            self::PENDING => "Menunggu",
            self::PROCESSING => "Diproses",
            self::SHIPPED => "Dikirim",
            self::DELIVERED => "Terkirim",
            self::CANCELLED => "Dibatalkan",
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED]);
    }
}

$status = StatusOrder::SHIPPED;
echo "Status: {$status->value} ({$status->label()})\n";
echo "Final?: " . ($status->isFinal() ? "ya" : "tidak") . "\n";

// Enum backed (pure enum)
enum Warna
{
    case Merah;
    case Hijau;
    case Biru;
}

function catatWarna(Warna $warna): string
{
    return match ($warna) {
        Warna::Merah => "Merah menyala",
        Warna::Hijau => "Hijau daun",
        Warna::Biru => "Biru laut",
    };
}

echo catatWarna(Warna::Merah) . "\n";

// Enum dari value
$statusFromString = StatusOrder::from("cancelled");
echo "From cancelled: {$statusFromString->label()}\n";

// ----- 12. TYPE VARIANCE (COVARIANCE & CONTRAVARIANCE) -----

echo "\n=== TYPE VARIANCE ===\n";

// Covariance: child class bisa return type lebih spesifik
abstract class Animal {}
class Cat extends Animal {}
class Dog extends Animal {}

abstract class AnimalShelter
{
    abstract public function adopt(): Animal;
}

class CatShelter extends AnimalShelter
{
    public function adopt(): Cat  // return lebih spesifik
    {
        return new Cat();
    }
}

$shelter = new CatShelter();
$hewan = $shelter->adopt();
echo "Class: " . $hewan::class . "\n";

// Contravariance: parameter bisa lebih general (dengan union)
interface Feedable
{
    public function feed(Animal $animal): void;
}

class FeedAnyAnimal implements Feedable
{
    // Bisa union type yang lebih general
    public function feed(Cat|Dog $animal): void
    {
        echo "Feed " . $animal::class . "\n";
    }
}

// ----- 13. NAMESPACE -----

echo "\n=== NAMESPACE ===\n";

// Biasanya namespace dipisah per file
// Contoh deklarasi:
// namespace App\Models;
// namespace App\Controllers;

// Use statement
// use App\Models\User;
// use App\Controllers\AuthController;
// use App\Utils\{Database, Logger};  // multi-import (PHP 7.0+)

// Alias
// use App\Models\Product as ProductModel;
// use DateTime as DT;

echo "Namespace digunakan untuk mengorganisir kode\n";

// ----- 14. OBJECT COMPARISON & CLONING -----

echo "\n=== OBJECT COMPARISON & CLONING ===\n";

class Titik
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}
}

$p1 = new Titik(1, 2);
$p2 = new Titik(1, 2);
$p3 = $p1;

echo "p1 == p2: " . ($p1 == $p2 ? "true" : "false") . "\n";   // true (sama properti)
echo "p1 === p2: " . ($p1 === $p2 ? "true" : "false") . "\n"; // false (beda objek)
echo "p1 === p3: " . ($p1 === $p3 ? "true" : "false") . "\n"; // true (referensi sama)

// Clone
class DataMahasiswa
{
    public function __construct(
        public string $nama,
        public array $nilai = [],
    ) {}

    public function __clone(): void
    {
        // Deep copy array
        $this->nilai = array_map(fn($n) => $n, $this->nilai);
    }
}

$asli = new DataMahasiswa("Budi", [85, 90, 78]);
$clone = clone $asli;
$clone->nama = "Ani";
$clone->nilai[] = 100;

echo "Asli: {$asli->nama} - " . implode(", ", $asli->nilai) . "\n";
echo "Clone: {$clone->nama} - " . implode(", ", $clone->nilai) . "\n";

// ----- 15. Anonymous Class -----

echo "\n=== ANONYMOUS CLASS ===\n";

interface Greeter
{
    public function greet(string $nama): string;
}

$greeter = new class implements Greeter {
    public function greet(string $nama): string
    {
        return "Halo, $nama! (from anonymous class)";
    }
};

echo $greeter->greet("Budi") . "\n";

// Anonymous class dengan constructor
$logger = new class("/tmp/app.log") {
    public function __construct(
        private string $path,
    ) {}

    public function write(string $pesan): void
    {
        file_put_contents($this->path, $pesan . "\n", FILE_APPEND);
    }
};

echo "Anonymous class logger dibuat\n";

echo "\nSelesai belajar OOP di PHP!\n";
