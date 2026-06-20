<?php

// ============================================
// 07 - MAGIC METHODS
// ============================================
// Topik: __construct, __destruct, __get, __set,
//        __call, __callStatic, __toString,
//        __invoke, __clone, __debugInfo,
//        __isset, __unset, __sleep, __wakeup,
//        __serialize, __unserialize
// ============================================

// ----- 1. __construct & __destruct -----

echo "=== CONSTRUCT & DESTRUCT ===\n";

class KoneksiDatabase
{
    private ?PDO $pdo = null;

    public function __construct(
        private string $dsn,
        private string $user,
        private string $password,
    ) {
        echo "[__construct] Membuat koneksi ke database...\n";
        // $this->pdo = new PDO($dsn, $user, $password);
    }

    public function query(string $sql): string
    {
        return "Eksekusi: $sql\n";
    }

    public function __destruct()
    {
        echo "[__destruct] Menutup koneksi database...\n";
        $this->pdo = null;
    }
}

$db = new KoneksiDatabase("mysql:host=localhost", "root", "");
echo $db->query("SELECT * FROM users");
// __destruct dipanggil otomatis saat objek tidak dipakai
unset($db);

echo "\n";

// ----- 2. __get & __set (Property Overloading) -----

echo "=== __get & __set ===\n";

class ConfigDinamis
{
    private array $data = [];

    // Dipanggil saat ngakses property yang tidak ada / protected / private
    public function __get(string $name): mixed
    {
        echo "[__get] Mengakses: $name\n";
        return $this->data[$name] ?? null;
    }

    // Dipanggil saat ngeset property yang tidak ada / protected / private
    public function __set(string $name, mixed $value): void
    {
        echo "[__set] Men-set: $name = " . (is_scalar($value) ? $value : json_encode($value)) . "\n";
        $this->data[$name] = $value;
    }

    // Dipanggil saat isset() dipanggil pada property
    public function __isset(string $name): bool
    {
        echo "[__isset] Cek: $name\n";
        return isset($this->data[$name]);
    }

    // Dipanggil saat unset() dipanggil pada property
    public function __unset(string $name): void
    {
        echo "[__unset] Hapus: $name\n";
        unset($this->data[$name]);
    }
}

$config = new ConfigDinamis();
$config->app_name = "BelajarPHP";  // __set
$config->debug = true;              // __set

echo $config->app_name . "\n";     // __get
echo $config->debug . "\n";        // __get
echo $config->tidakAda . "\n";     // __get -> null

echo isset($config->app_name) ? "ada\n" : "tidak ada\n";  // __isset
unset($config->app_name);                                  // __unset

echo "\n";

// ----- 3. __call & __callStatic (Method Overloading) -----

echo "=== __call & __callStatic ===\n";

class QueryBuilder
{
    private array $clauses = [];

    // Dipanggil saat method non-static tidak ditemukan
    public function __call(string $name, array $arguments): self
    {
        $prefix = strtolower(preg_replace('/[A-Z]/', '_$0', $name));
        $this->clauses[] = "$prefix: " . implode(", ", $arguments);
        return $this;
    }

    // Dipanggil saat method static tidak ditemukan
    public static function __callStatic(string $name, array $arguments): mixed
    {
        echo "[__callStatic] Memanggil: $name dengan args: " . implode(", ", $arguments) . "\n";
        return null;
    }

    public function get(): string
    {
        return implode(" | ", $this->clauses);
    }
}

$qb = new QueryBuilder();
$result = $qb->where("id", "=", "1")
             ->orderBy("created_at", "DESC")
             ->limit("10");
echo $result->get() . "\n";

// __callStatic
QueryBuilder::someStaticMethod("arg1", "arg2");

echo "\n";

// ----- 4. __toString -----

echo "=== __toString ===\n";

class UserProfile
{
    public function __construct(
        private string $nama,
        private int $umur,
        private string $email,
    ) {}

    // Dipanggil saat objek di-convert ke string
    public function __toString(): string
    {
        return "User: {$this->nama} ({$this->umur} th) - {$this->email}";
    }
}

$profil = new UserProfile("Budi", 25, "budi@test.com");
echo $profil . "\n";  // otomatis panggil __toString

// String concatenation
echo "Data: " . $profil . "\n";

echo "\n";

// ----- 5. __invoke -----

echo "=== __invoke ===\n";

class Calculator
{
    private string $lastResult = "";

    // Dipanggil saat objek dipanggil sebagai function
    public function __invoke(string $operator, int|float ...$angka): int|float
    {
        $result = match ($operator) {
            "+" => array_sum($angka),
            "*" => array_product($angka),
            "-" => array_shift($angka) - array_sum($angka),
            "/" => array_reduce($angka, fn($carry, $n) => $n != 0 ? $carry / $n : $carry, array_shift($angka)),
            default => throw new InvalidArgumentException("Operator tidak dikenal"),
        };

        $this->lastResult = implode(" $operator ", $angka) . " = $result";
        return $result;
    }

    public function getLastResult(): string
    {
        return $this->lastResult;
    }
}

$calc = new Calculator();
echo $calc("+", 1, 2, 3, 4, 5) . "\n";   // 15
echo $calc("*", 2, 3, 4) . "\n";          // 24
echo $calc->getLastResult() . "\n";

// Bisa dipakai untuk callback
$angkaArr = [1, 2, 3, 4];
$kaliDua = array_map(fn($n) => $calc("*", $n, 2), $angkaArr);
echo "Kali dua: " . implode(", ", $kaliDua) . "\n";

// Bisa dipakai sebagai callable
if (is_callable($calc)) {
    echo "Calculator is callable!\n";
}

echo "\n";

// ----- 6. __clone -----

echo "=== __clone ===\n";

class OrderItem
{
    public function __construct(
        public string $product,
        public int $qty,
        public float $harga,
    ) {}

    public function getSubtotal(): float
    {
        return $this->qty * $this->harga;
    }
}

class Order
{
    public array $items = [];
    public string $status = "pending";

    public function __construct(
        public string $orderId,
    ) {}

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }

    // Dipanggil setelah objek di-clone
    public function __clone(): void
    {
        echo "[__clone] Deep cloning order {$this->orderId}...\n";
        // Deep copy items (biar ga referensi ke objek yang sama)
        $this->items = array_map(fn($item) => clone $item, $this->items);
        $this->orderId = $this->orderId . "-copy";
    }
}

$order1 = new Order("ORD-001");
$order1->addItem(new OrderItem("Laptop", 1, 15000000));
$order1->addItem(new OrderItem("Mouse", 2, 250000));

$order2 = clone $order1;
$order2->items[0]->qty = 2;  // ini cuma ngaruh ke order2 karena deep copy

echo "Order1: {$order1->orderId} - items[0] qty: {$order1->items[0]->qty}\n";
echo "Order2: {$order2->orderId} - items[0] qty: {$order2->items[0]->qty}\n";

echo "\n";

// ----- 7. __debugInfo -----

echo "=== __debugInfo ===\n";

class UserSensitif
{
    public function __construct(
        public string $nama,
        public string $email,
        private string $password,
        private string $token,
    ) {}

    // Mengontrol output var_dump()
    public function __debugInfo(): array
    {
        return [
            "nama" => $this->nama,
            "email" => $this->email,
            "password" => str_repeat("*", strlen($this->password)),
            "token" => substr($this->token, 0, 8) . "...",
        ];
    }
}

$sensitif = new UserSensitif("Budi", "budi@test.com", "rahasia123", "eyJhbGciOiJIUzI1NiIs...");
var_dump($sensitif);

echo "\n";

// ----- 8. __sleep & __wakeup (serialization legacy) -----

echo "=== __sleep & __wakeup ===\n";

class SessionData
{
    private array $data = [];
    private ?PDO $koneksi = null;
    private string $tmpCache = "";

    public function __construct()
    {
        echo "[construct] Session dibuat\n";
    }

    // Dipanggil saat serialize(). Harus return array property yang akan di-serialize
    public function __sleep(): array
    {
        echo "[__sleep] Akan di-serialize (koneksi & cache di-exclude)\n";
        return ["data"];  // hanya data yang di-serialize, koneksi & tmpCache tidak
    }

    // Dipanggil saat unserialize(). Buat restore resource
    public function __wakeup(): void
    {
        echo "[__wakeup] Restore setelah unserialize\n";
        $this->koneksi = null;  // koneksi di-reconnect
        $this->tmpCache = "";
    }
}

$session = new SessionData();
$serialized = serialize($session);
echo "Serialized: $serialized\n";
$restored = unserialize($serialized);

echo "\n";

// ----- 9. __serialize & __unserialize (PHP 7.4+, prefered) -----

echo "=== __serialize & __unserialize ===\n";

class CacheManager
{
    private array $cache = [];
    private string $cacheDir;
    private array $tempData = [];

    public function __construct(string $cacheDir = "/tmp/cache")
    {
        $this->cacheDir = $cacheDir;
        echo "[construct] CacheManager untuk $cacheDir\n";
    }

    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }

    // Serialization modern. Harus return array
    public function __serialize(): array
    {
        echo "[__serialize] Serializing...\n";
        return [
            "cache" => $this->cache,
            "cacheDir" => $this->cacheDir,
            // tempData sengaja di-exclude
        ];
    }

    // Unserialization modern. Restore dari array
    public function __unserialize(array $data): void
    {
        echo "[__unserialize] Unserializing...\n";
        $this->cache = $data["cache"];
        $this->cacheDir = $data["cacheDir"];
        $this->tempData = [];  // reset
    }
}

$cache = new CacheManager();
$cache->set("user_1", ["nama" => "Budi", "role" => "admin"]);

$ser = serialize($cache);
echo "Serialized length: " . strlen($ser) . "\n";

$unser = unserialize($ser);
var_dump($unser);

echo "\n";

// ----- 10. PRACTICAL: Active Record Style -----

echo "=== PRACTICAL: Active Record ===\n";

abstract class Model
{
    protected array $attributes = [];
    protected array $original = [];
    private static array $registry = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    // __get untuk akses attribute
    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        // Cek method accessor (get{Name}Attribute)
        $method = "get" . ucfirst($name) . "Attribute";
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }

    // __set untuk set attribute
    public function __set(string $name, mixed $value): void
    {
        // Cek mutator (set{Name}Attribute)
        $method = "set" . ucfirst($name) . "Attribute";
        if (method_exists($this, $method)) {
            $this->$method($value);
            return;
        }

        $this->attributes[$name] = $value;
    }

    // __call untuk query scope / dynamic method
    public function __call(string $name, array $arguments): mixed
    {
        // Cek scope (scope{Name})
        $scopeMethod = "scope" . ucfirst($name);
        if (method_exists($this, $scopeMethod)) {
            return $this->$scopeMethod(...$arguments);
        }

        // Cek relasi / shortcut
        if (str_starts_with($name, "findBy")) {
            $field = strtolower(substr($name, 6));
            return $this->findBy($field, $arguments[0] ?? null);
        }

        throw new BadMethodCallException("Method $name tidak ditemukan");
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        $instance = new static();
        return $instance->$name(...$arguments);
    }

    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;  // trigger __set
        }
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function __serialize(): array
    {
        return $this->attributes;
    }

    public function __unserialize(array $data): void
    {
        $this->attributes = $data;
    }

    public function __debugInfo(): array
    {
        return $this->attributes;
    }

    private function findBy(string $field, mixed $value): ?static
    {
        echo "[findBy] Mencari $field = $value\n";
        return null;
    }

    protected function scopeAktif(): static
    {
        $this->attributes["status"] = "aktif";
        return $this;
    }
}

class UserModel extends Model
{
    public function getFullNameAttribute(): string
    {
        return trim(($this->attributes["first_name"] ?? "")
            . " " . ($this->attributes["last_name"] ?? ""));
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes["password"] = password_hash($value, PASSWORD_DEFAULT);
    }

    protected function scopeAdmin(): static
    {
        $this->attributes["role"] = "admin";
        return $this;
    }
}

$user = new UserModel([
    "first_name" => "Budi",
    "last_name" => "Santoso",
    "email" => "budi@test.com",
]);

$user->password = "rahasia123";  // trigger setPasswordAttribute
echo "Full name: " . $user->full_name . "\n";  // trigger getFullNameAttribute
echo "Password hash: " . $user->password . "\n";

// __callStatic
UserModel::findByEmail("budi@test.com");

// Magic scope
$user->aktif();  // panggil scopeAktif
var_dump($user);

echo "\n";

// ----- 11. COMPLETE EXAMPLE: Proxy Pattern -----

echo "=== PROXY PATTERN ===\n";

class HeavyService
{
    private array $data = [];

    public function __construct()
    {
        echo "[HeavyService] Loading data mahal...\n";
        sleep(1);  // simulasi loading lama
        $this->data = range(1, 100);
    }

    public function getData(): array
    {
        return $this->data;
    }
}

class HeavyServiceProxy
{
    private ?HeavyService $service = null;

    public function __construct()
    {
        echo "[Proxy] Proxy siap, service belum di-load\n";
    }

    // Lazy loading via __call
    public function __call(string $name, array $arguments): mixed
    {
        // Init service di sini (lazy)
        if ($this->service === null) {
            echo "[Proxy] Inisialisasi service (lazy)...\n";
            $this->service = new HeavyService();
        }

        if (!method_exists($this->service, $name)) {
            throw new BadMethodCallException("Method $name tidak ada");
        }

        return $this->service->$name(...$arguments);
    }

    // Delegasi property
    public function __get(string $name): mixed
    {
        if ($this->service === null) {
            $this->service = new HeavyService();
        }
        return $this->service->$name;
    }
}

$proxy = new HeavyServiceProxy();
echo "Proxy dibuat, tapi service belum di-load...\n";

// Baru di-load saat dipanggil
$data = $proxy->getData();
echo "Data length: " . count($data) . "\n";

echo "\nSelesai belajar magic methods!\n";
