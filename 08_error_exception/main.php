<?php

// ============================================
// 08 - ERROR & EXCEPTION HANDLING
// ============================================
// Topik: Try/Catch/Finally, Custom Exception,
//        Error Handler, Exception Handler,
//        Shutdown Handler, Assertions
// ============================================

require_once __DIR__ . '/exceptions.php';
require_once __DIR__ . '/handlers.php';
require_once __DIR__ . '/assertions.php';

// ----- 1. BASIC TRY/CATCH -----

echo "=== TRY / CATCH / FINALLY ===\n";

function bagi(int $a, int $b): float
{
    if ($b === 0) {
        throw new DivisionByZeroError("Tidak bisa membagi dengan nol");
    }
    return $a / $b;
}

try {
    $hasil = bagi(10, 0);
    echo "Hasil: $hasil\n";
} catch (DivisionByZeroError $e) {
    echo "[Catch] " . $e->getMessage() . "\n";
} finally {
    echo "[Finally] Blok ini selalu dijalankan\n";
}

echo "\n";

// ----- 2. MULTIPLE CATCH -----

echo "=== MULTIPLE CATCH ===\n";

function prosesData(array $data): string
{
    if (empty($data)) {
        throw new InvalidArgumentException("Data tidak boleh kosong");
    }

    if (!isset($data['email'])) {
        throw new ValidationException("Email wajib diisi");
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException("Format email tidak valid", [
            'email' => ['Format email salah']
        ]);
    }

    if ($data['role'] === 'admin' && $data['user_id'] === 1) {
        throw new AuthorizationException("User ini tidak bisa dijadikan admin");
    }

    return "Data valid untuk: {$data['email']}";
}

$testData = [
    'email' => 'budi@test.com',
    'role' => 'admin',
    'user_id' => 1,
];

try {
    echo prosesData($testData) . "\n";
} catch (ValidationException $e) {
    echo "[Validation Error] {$e->getMessage()}\n";
    foreach ($e->getErrors() as $field => $errors) {
        echo "  - $field: " . implode(", ", $errors) . "\n";
    }
} catch (AuthorizationException $e) {
    echo "[Auth Error] {$e->getMessage()}\n";
} catch (InvalidArgumentException $e) {
    echo "[Arg Error] {$e->getMessage()}\n";
} catch (Throwable $e) {
    echo "[Unknown Error] " . $e->getMessage() . "\n";
}

echo "\n";

// ----- 3. CUSTOM EXCEPTION HIERARCHY -----

echo "=== CUSTOM EXCEPTION HIERARCHY ===\n";

function cariUser(int $id): array
{
    $users = [
        1 => ['nama' => 'Budi', 'email' => 'budi@test.com'],
        2 => ['nama' => 'Ani', 'email' => 'ani@test.com'],
    ];

    if (!isset($users[$id])) {
        throw new NotFoundException("User dengan ID $id tidak ditemukan");
    }

    return $users[$id];
}

function login(string $email, string $password): string
{
    if (empty($email) || empty($password)) {
        throw new ValidationException("Email dan password wajib diisi");
    }

    if ($email !== 'admin@test.com' || $password !== 'admin123') {
        throw new AuthenticationException("Email atau password salah");
    }

    return "Bearer token-simulasi-123";
}

// Test hierarchy
try {
    $user = cariUser(99);
    print_r($user);
} catch (NotFoundException $e) {
    echo "[404] {$e->getMessage()}\n";
}

try {
    login('wrong@test.com', 'wrong');
} catch (AuthenticationException $e) {
    echo "[401] {$e->getMessage()}\n";
}

echo "\n";

// ----- 4. NESTED TRY/CATCH & CHAINING -----

echo "=== NESTED TRY/CATCH & EXCEPTION CHAINING ===\n";

function prosesOrder(array $order): string
{
    try {
        // Validasi input
        if (empty($order['items'])) {
            throw new ValidationException("Order harus memiliki items");
        }

        try {
            $total = 0;
            foreach ($order['items'] as $item) {
                if ($item['price'] < 0) {
                    throw new InvalidArgumentException("Harga tidak boleh negatif: {$item['name']}");
                }
                $total += $item['price'] * $item['qty'];
            }

            if ($total > 10000000) {
                throw new ValidationException("Total order melebihi batas: Rp" . number_format($total));
            }

            return "Order berhasil, total: Rp" . number_format($total);

        } catch (InvalidArgumentException $e) {
            throw new ValidationException(
                "Item order tidak valid",
                ['items' => [$e->getMessage()]],
                400,
                $e,
            );
        }

    } catch (ValidationException $e) {
        echo "[Terjadi error saat proses order]\n";
        throw new DatabaseException(
            "Gagal menyimpan order: " . $e->getMessage(),
            "23000",
            500,
            $e,
        );
    }
}

try {
    echo prosesOrder([
        'items' => [
            ['name' => 'Laptop', 'price' => 15000000, 'qty' => 1],
            ['name' => 'Mouse', 'price' => -5000, 'qty' => 1],
        ],
    ]) . "\n";
} catch (Throwable $e) {
    echo "[Top Level] {$e->getMessage()}\n";
    echo "Previous: " . ($e->getPrevious()?->getMessage() ?? "none") . "\n";
}

echo "\n";

// ----- 5. TRY/CATCH DENGAN FINALLY UNTUK RESOURCE -----

echo "=== FINALLY UNTUK RESOURCE CLEANUP ===\n";

class FileProcessor
{
    private ?string $filePath = null;
    private bool $opened = false;

    public function open(string $path): void
    {
        $this->filePath = $path;
        $this->opened = true;
        echo "[Resource] File dibuka: $path\n";
    }

    public function process(): string
    {
        if (!$this->opened) {
            throw new RuntimeException("File belum dibuka");
        }
        echo "[Process] Memproses file...\n";
        return "konten file";
    }

    public function close(): void
    {
        if ($this->opened) {
            echo "[Resource] File ditutup: {$this->filePath}\n";
            $this->opened = false;
        }
    }
}

function bacaData(string $path): string
{
    $processor = new FileProcessor();
    try {
        $processor->open($path);
        return $processor->process();
    } catch (Throwable $e) {
        echo "[Error saat baca data] {$e->getMessage()}\n";
        throw $e;
    } finally {
        $processor->close();
    }
}

try {
    echo bacaData("/tmp/test.txt") . "\n";
} catch (Throwable $e) {
    echo "[Caught] {$e->getMessage()}\n";
}

echo "\n";

// ----- 6. ERROR HANDLER -----

echo "=== ERROR HANDLER ===\n";

// Register custom handlers
ErrorHandler::register();

// Trigger warning (tidak throw, tapi error handler menangkap)
echo strpos([], "test") . "\n";

// Trigger notice
echo $undefinedVariable . "\n";

echo "\n";

// ----- 7. EXCEPTION HANDLER -----

echo "=== EXCEPTION HANDLER ===\n";

// Uncaught exception akan ditangkap exception handler
// throw new NotFoundException("Halaman tidak ditemukan");

echo "Exception handler sudah terdaftar (uncomment throw untuk test)\n";

echo "\n";

// ----- 8. ASSERTIONS -----

echo "=== ASSERTIONS ===\n";

// Aktifkan assertion
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_BAIL, 0);

try {
    $validData = [
        ['name' => 'Budi', 'email' => 'budi@test.com', 'age' => 20, 'password' => 'rahasia123'],
        ['name' => 'Ani', 'email' => 'ani@test.com', 'age' => 25, 'password' => 'password456'],
    ];

    $result = DataProcessor::process($validData);
    echo "Data valid: " . count($result) . " records\n";

} catch (AssertionError $e) {
    echo "[Assertion failed] " . $e->getMessage() . "\n";
}

// Test assertion error
echo "\nTest assertion dengan data invalid:\n";

try {
    $invalidData = [
        ['name' => '', 'email' => 'bukan-email', 'age' => 15, 'password' => 'short'],
    ];

    DataProcessor::process($invalidData);
} catch (AssertionError $e) {
    echo "[Assertion failed] " . $e->getMessage() . "\n";
}

echo "\n";

// ----- 9. DEBUG HELPER -----

echo "=== DEBUG HELPER ===\n";

try {
    throw new RateLimitException(60, "Terlalu banyak percobaan login");
} catch (RateLimitException $e) {
    DebugHandler::prettyPrint($e);
    echo "Coba lagi setelah {$e->getRetryAfter()} detik\n";
}

echo "\nSelesai belajar error & exception handling!\n";
