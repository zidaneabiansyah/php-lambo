<?php

// ============================================
// 19 - FIBERS & ATTRIBUTES (PHP 8.1+)
// ============================================
// Topik: Fibers (cooperative multitasking),
//        Attributes (metadata/annotations),
//        Reflection, Built-in Attributes
// ============================================

echo "==========================================\n";
echo "  FIBERS & ATTRIBUTES (PHP 8.1+)\n";
echo "==========================================\n\n";

// ============================================
// BAGIAN A: FIBERS
// ============================================
// Fiber adalah lightweight thread yang memungkinkan
// cooperative multitasking di PHP.
// Fiber di-maintain di userspace, bukan kernel.

echo "--- 1. BASIC FIBER ---\n\n";

// Membuat Fiber sederhana
$fiber = new Fiber(function (): void {
    echo "  Fiber: Mulai berjalan\n";
    Fiber::suspend(); // Pause fiber
    echo "  Fiber: Dilanjutkan setelah suspend\n";
    Fiber::suspend('data dari fiber');
    echo "  Fiber: Selesai\n";
});

echo "Main: Fiber belum start\n";
$fiber->start();        // Mulai fiber
echo "Main: Fiber di-pause (suspend)\n";
$fiber->resume();       // Lanjutkan fiber
echo "Main: Fiber di-pause lagi\n";
$value = $fiber->resume(); // Kirim data & lanjutkan
echo "Main: Menerima dari fiber: $value\n";
$fiber->resume();       // Selesaikan fiber
echo "Main: Fiber selesai\n\n";


echo "--- 2. PASSING DATA KE FIBER ---\n\n";

// Data bisa dikirim saat suspend dan start/resume
$calculator = new Fiber(function (int $a, int $b): int {
    echo "  Fiber: Menghitung $a + $b\n";
    $operator = Fiber::suspend('masukkan operator');

    if ($operator === '+') {
        return $a + $b;
    } elseif ($operator === '*') {
        return $a * $b;
    }
    return 0;
});

$result = $calculator->start(10, 20); // Kirim argumen ke fiber
echo "Main: Fiber minta operator (suspend dengan: $result)\n";

$op = $calculator->resume('*'); // Kirim operator ke fiber
$result = $calculator->getReturn();
echo "Main: Hasil perhitungan: $result\n\n";


echo "--- 3. FIBER STATUS ---\n\n";

$statusFiber = new Fiber(function (): string {
    return 'selesai';
});

echo "  Status sebelum start: " . ($statusFiber->isStarted() ? 'started' : 'not started') . "\n";
echo "  Status: " . ($statusFiber->isSuspended() ? 'suspended' : 'not suspended') . "\n";
echo "  Status: " . ($statusFiber->isRunning() ? 'running' : 'not running') . "\n";
echo "  Status: " . ($statusFiber->isTerminated() ? 'terminated' : 'not terminated') . "\n\n";

$statusFiber->start();
echo "\n  Status setelah start: " . ($statusFiber->isStarted() ? 'started' : 'not started') . "\n";
echo "  Status: " . ($statusFiber->isTerminated() ? 'terminated' : 'not terminated') . "\n\n";


echo "--- 4. FIBER: RETURN VALUE & ERROR HANDLING ---\n\n";

$robustFiber = new Fiber(function (int $numerator, int $denominator): float {
    if ($denominator === 0) {
        throw new \DivisionByZeroError("Tidak bisa bagi nol!");
    }
    Fiber::suspend('Membagi...');
    return $numerator / $denominator;
});

try {
    $robustFiber->start(100, 5);
    echo "  Fiber suspend\n";
    $robustFiber->resume();
    echo "  Hasil: " . $robustFiber->getReturn() . "\n";
} catch (\DivisionByZeroError $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// Test dengan pembagian nol
$zeroFiber = new Fiber(function (int $a, int $b): float {
    if ($b === 0) {
        throw new \DivisionByZeroError("Division by zero!");
    }
    return $a / $b;
});

try {
    $zeroFiber->start(10, 0);
} catch (\DivisionByZeroError $e) {
    echo "  Error caught: " . $e->getMessage() . "\n\n";
}


echo "--- 5. FIBER SCHEDULER (MULTI-TASKING) ---\n\n";

class FiberScheduler
{
    private array $fibers = [];

    public function add(Fiber $fiber): self
    {
        $this->fibers[] = $fiber;
        return $this;
    }

    public function run(): void
    {
        while (!empty($this->fibers)) {
            $fiber = array_shift($this->fibers);

            if (!$fiber->isStarted()) {
                $fiber->start();
            } elseif ($fiber->isSuspended()) {
                $fiber->resume();
            }

            if (!$fiber->isTerminated()) {
                $this->fibers[] = $fiber;
            }
        }
    }
}

$scheduler = new FiberScheduler();

$scheduler->add(new Fiber(function (): void {
    for ($i = 1; $i <= 3; $i++) {
        echo "  Task A: langkah $i\n";
        Fiber::suspend();
    }
}));

$scheduler->add(new Fiber(function (): void {
    for ($i = 1; $i <= 3; $i++) {
        echo "  Task B: langkah $i\n";
        Fiber::suspend();
    }
}));

echo "  Running scheduler:\n";
$scheduler->run();
echo "\n";


echo "--- 6. FIBER POOL ---\n\n";

class FiberPool
{
    private array $queue = [];
    private int $maxConcurrent;
    private int $running = 0;

    public function __construct(int $maxConcurrent = 3)
    {
        $this->maxConcurrent = $maxConcurrent;
    }

    public function submit(callable $task): void
    {
        $this->queue[] = new Fiber($task);
    }

    public function run(): void
    {
        while (!empty($this->queue) || $this->running > 0) {
            while (!empty($this->queue) && $this->running < $this->maxConcurrent) {
                $fiber = array_shift($this->queue);
                $fiber->start();
                $this->running++;
            }

            // Simulasi: dalam real use, ini akan di-await
            foreach ($this->queue as $i => $fiber) {
                if ($fiber->isSuspended()) {
                    $fiber->resume();
                    if ($fiber->isTerminated()) {
                        $this->running--;
                        unset($this->queue[$i]);
                    }
                }
            }
            break; // Untuk demo, kita break
        }
    }
}

$pool = new FiberPool(2);
$pool->submit(function (): void {
    echo "  Pool worker 1: memproses data\n";
    Fiber::suspend();
    echo "  Pool worker 1: selesai\n";
});
$pool->submit(function (): void {
    echo "  Pool worker 2: memproses data\n";
    Fiber::suspend();
    echo "  Pool worker 2: selesai\n";
});
echo "  Fiber Pool:\n";
$pool->run();
echo "\n";


echo "--- 7. FIBER UNTUK ASYNC SIMULATION ---\n\n";

// Simulasi async I/O dengan Fiber
function asyncRead(string $filename): Fiber
{
    return new Fiber(function () use ($filename): string {
        // Simulasi reading file
        Fiber::suspend("Reading $filename...");
        $content = "Isi file $filename";
        Fiber::suspend($content);
        return $content;
    });
}

function asyncWrite(string $filename, string $data): Fiber
{
    return new Fiber(function () use ($filename, $data): string {
        Fiber::suspend("Writing to $filename...");
        $result = "Written " . strlen($data) . " bytes to $filename";
        Fiber::suspend($result);
        return $result;
    });
}

$reader = asyncRead('data.txt');
$writer = asyncWrite('output.txt', 'Hello World');

// Interleave execution
$reader->start();
$writer->start();

echo "  " . $reader->resume() . "\n";
echo "  " . $writer->resume() . "\n";
echo "  " . $reader->resume() . "\n";
echo "  " . $writer->resume() . "\n";
echo "\n";


// ============================================
// BAGIAN B: ATTRIBUTES
// ============================================
// Attributes (dikenal sebagai annotations di bahasa lain)
// adalah metadata yang bisa ditambahkan ke class, method,
// property, atau parameter.

echo "==========================================\n";
echo "  ATTRIBUTES\n";
echo "==========================================\n\n";

echo "--- 8. BASIC ATTRIBUTE DECLARATION ---\n\n";

// Deklarasi attribute
#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET'
    ) {}
}

#[Attribute(Attribute::TARGET_METHOD)]
class Middleware
{
    public function __construct(
        public string $name
    ) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Validate
{
    public function __construct(
        public string $rule,
        public ?string $message = null
    ) {}
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Tag
{
    public function __construct(
        public string $name
    ) {}
}

// Penggunaan attribute
#[Route(path: '/users', method: 'GET')]
#[Tag(name: 'api')]
#[Tag(name: 'v1')]
class UserController
{
    #[Middleware(name: 'auth')]
    public function index(): string
    {
        return 'User list';
    }

    #[Middleware(name: 'guest')]
    public function login(): string
    {
        return 'Login page';
    }

    #[Validate(rule: 'required|string')]
    private string $name;

    #[Validate(rule: 'required|email', message: 'Email tidak valid')]
    private string $email;
}

echo "  Attributes dideklarasikan dan digunakan\n\n";


echo "--- 9. READING ATTRIBUTES VIA REFLECTION ---\n\n";

// Membaca attribute dari class
$reflection = new ReflectionClass(UserController::class);

// Class attributes
$classAttrs = $reflection->getAttributes();
echo "  Class attributes:\n";
foreach ($classAttrs as $attr) {
    $instance = $attr->newInstance();
    echo "    - " . $attr->getName() . ": path={$instance->path}, method={$instance->method}\n";
}

// Method attributes
$methods = ['index', 'login'];
foreach ($methods as $methodName) {
    $method = $reflection->getMethod($methodName);
    $methodAttrs = $method->getAttributes();
    echo "\n  Method '$methodName' attributes:\n";
    foreach ($methodAttrs as $attr) {
        $instance = $attr->newInstance();
        echo "    - " . $attr->getName() . ": name={$instance->name}\n";
    }
}

// Property attributes
$properties = ['name', 'email'];
foreach ($properties as $propName) {
    $property = $reflection->getProperty($propName);
    $propAttrs = $property->getAttributes();
    echo "\n  Property '$propName' attributes:\n";
    foreach ($propAttrs as $attr) {
        $instance = $attr->newInstance();
        echo "    - " . $attr->getName() . ": rule={$instance->rule}";
        if ($instance->message) {
            echo ", message={$instance->message}";
        }
        echo "\n";
    }
}
echo "\n";


echo "--- 10. PRACTICAL: ATTRIBUTE-BASED ROUTING ---\n\n";

// Sistem routing berbasis attribute
class AttributeRouter
{
    private array $routes = [];

    public function register(string $controllerClass): void
    {
        $reflection = new ReflectionClass($controllerClass);
        $classAttrs = $reflection->getAttributes(Route::class);

        foreach ($classAttrs as $classAttr) {
            $route = $classAttr->newInstance();

            foreach ($reflection->getMethods() as $method) {
                $methodAttrs = $method->getAttributes(Route::class);

                foreach ($methodAttrs as $methodAttr) {
                    $methodRoute = $methodAttr->newInstance();
                    $fullPath = rtrim($route->path . '/' . $method->getName(), '/');

                    $this->routes[] = [
                        'path' => $fullPath,
                        'method' => $methodRoute->method,
                        'controller' => $controllerClass,
                        'action' => $method->getName(),
                    ];
                }
            }
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function match(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                return $route;
            }
        }
        return null;
    }
}

// Controller dengan attribute routing
#[Route(path: '/api/posts')]
class PostController
{
    #[Route(path: '/api/posts', method: 'GET')]
    public function index(): string { return 'List posts'; }

    #[Route(path: '/api/posts', method: 'POST')]
    public function store(): string { return 'Create post'; }

    #[Route(path: '/api/posts/{id}', method: 'GET')]
    public function show(): string { return 'Show post'; }

    #[Route(path: '/api/posts/{id}', method: 'DELETE')]
    public function destroy(): string { return 'Delete post'; }
}

$router = new AttributeRouter();
$router->register(PostController::class);
$router->register(UserController::class);

echo "  Registered routes:\n";
foreach ($router->getRoutes() as $route) {
    echo "    {$route['method']} {$route['path']} -> {$route['controller']}::{$route['action']}\n";
}

// Test matching
$matched = $router->match('GET', '/api/posts');
echo "\n  Match GET /api/posts: " . ($matched ? $matched['controller'] . '::' . $matched['action'] : 'not found') . "\n";

$matched = $router->match('DELETE', '/api/posts');
echo "  Match DELETE /api/posts: " . ($matched ? $matched['controller'] . '::' . $matched['action'] : 'not found') . "\n\n";


echo "--- 11. ATTRIBUTE-BASED VALIDATION ---\n\n";

// Sistem validasi berbasis attribute
class Validator
{
    public static function validate(object $instance): array
    {
        $errors = [];
        $reflection = new ReflectionClass($instance);

        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(Validate::class);
            $property->setAccessible(true);
            $value = $property->getValue($instance);

            foreach ($attrs as $attr) {
                $validate = $attr->newInstance();
                $rules = explode('|', $validate->rule);

                foreach ($rules as $rule) {
                    $ruleName = explode(':', $rule)[0];
                    $ruleParam = explode(':', $rule)[1] ?? null;

                    $valid = match ($ruleName) {
                        'required' => $value !== null && $value !== '',
                        'string' => is_string($value),
                        'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                        'min' => strlen($value) >= (int)$ruleParam,
                        'max' => strlen($value) <= (int)$ruleParam,
                        default => true,
                    };

                    if (!$valid) {
                        $message = $validate->message ?? "{$property->getName()} gagal validasi: $ruleName";
                        $errors[$property->getName()][] = $message;
                    }
                }
            }
        }

        return $errors;
    }
}

class UserForm
{
    #[Validate(rule: 'required|string|min:3', message: 'Nama harus minimal 3 karakter')]
    public ?string $name = 'Jo';

    #[Validate(rule: 'required|email', message: 'Format email tidak valid')]
    public ?string $email = 'invalid-email';

    #[Validate(rule: 'required|string|max:100')]
    public ?string $bio = 'Hello';
}

$form = new UserForm();
$errors = Validator::validate($form);

echo "  Validation results:\n";
if (empty($errors)) {
    echo "    Semua validasi passed!\n";
} else {
    foreach ($errors as $field => $fieldErrors) {
        foreach ($fieldErrors as $error) {
            echo "    ✗ $error\n";
        }
    }
}
echo "\n";


echo "--- 12. BUILT-IN ATTRIBUTES ---\n\n";

// #[AllowDynamicProperties] - PHP 8.2+
// Membolehkan property dinamis di class
#[AllowDynamicProperties]
class DynamicClass
{
    public string $name = 'test';
}

$dynamic = new DynamicClass();
$dynamic->age = 25; // Property dinamis diizinkan karena ada attribute
echo "  AllowDynamicProperties: \$dynamic->age = {$dynamic->age}\n";

// #[\Override] - PHP 8.3+
// Menandai method yang override parent method
class BaseHandler
{
    public function handle(): string
    {
        return 'base';
    }
}

class SpecialHandler extends BaseHandler
{
    #[\Override]
    public function handle(): string
    {
        return 'special';
    }
}

$handler = new SpecialHandler();
echo "  Override: " . $handler->handle() . "\n\n";


echo "--- 13. ATTRIBUTE FOR CONFIGURATION ---\n\n";

// Menggunakan attribute untuk konfigurasi
#[Attribute(Attribute::TARGET_CLASS)]
class Config
{
    public function __construct(
        public string $name,
        public string $version = '1.0.0',
        public array $settings = []
    ) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Env
{
    public function __construct(
        public string $key,
        public mixed $default = null
    ) {}
}

#[Config(name: 'MyApp', version: '2.0.0', settings: ['debug' => true, 'timezone' => 'Asia/Jakarta'])]
class AppConfig
{
    #[Env(key: 'DB_HOST', default: 'localhost')]
    public string $dbHost;

    #[Env(key: 'DB_PORT', default: '3306')]
    public int $dbPort;

    #[Env(key: 'APP_DEBUG', default: false)]
    public bool $debug;
}

// Membaca konfigurasi dari attribute
function loadConfig(string $className): array
{
    $reflection = new ReflectionClass($className);
    $configAttrs = $reflection->getAttributes(Config::class);

    $config = [];
    if (!empty($configAttrs)) {
        $configObj = $configAttrs[0]->newInstance();
        $config['name'] = $configObj->name;
        $config['version'] = $configObj->version;
        $config['settings'] = $configObj->settings;
    }

    // Load dari environment variables
    $props = $reflection->getProperties();
    foreach ($props as $prop) {
        $envAttrs = $prop->getAttributes(Env::class);
        foreach ($envAttrs as $envAttr) {
            $env = $envAttr->newInstance();
            $value = getenv($env->key);
            $config[$prop->getName()] = $value !== false ? $value : $env->default;
        }
    }

    return $config;
}

$config = loadConfig(AppConfig::class);
echo "  App configuration from attributes:\n";
foreach ($config as $key => $value) {
    $display = is_array($value) ? json_encode($value) : var_export($value, true);
    echo "    $key: $display\n";
}
echo "\n";


echo "--- 14. ATTRIBUTE INHERITANCE ---\n\n";

// Attribute bisa diwarisi
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Author
{
    public function __construct(
        public string $name,
        public string $email = ''
    ) {}
}

// Class parent dengan attribute
#[Author(name: 'Budi', email: 'budi@example.com')]
class BaseService
{
    // Child class akan mewarisi attribute dari parent
}

// Membaca attribute dari parent
$baseReflection = new ReflectionClass(BaseService::class);
$authors = $baseReflection->getAttributes(Author::class);

echo "  Attributes di BaseService:\n";
foreach ($authors as $author) {
    $instance = $author->newInstance();
    echo "    Author: {$instance->name} ({$instance->email})\n";
}
echo "\n";


echo "==========================================\n";
echo "  RINGKASAN\n";
echo "==========================================\n";
echo "\n";
echo "FIBERS:\n";
echo "  - Light-weight cooperative threads\n";
echo "  - Fiber::suspend() untuk pause\n";
echo "  - start()/resume() untuk mulai/lanjutkan\n";
echo "  - getReturn() untuk ambil return value\n";
echo "  - Cocok untuk async I/O, scheduler, pool\n";
echo "\n";
echo "ATTRIBUTES:\n";
echo "  - Metadata untuk class/method/property/parameter\n";
echo "  - Dibaca via Reflection API\n";
echo "  - Flag: TARGET_CLASS, TARGET_METHOD, TARGET_PROPERTY\n";
echo "  - IS_REPEATABLE untuk multiple usage\n";
echo "  - Practical: routing, validation, config\n";
echo "\n";

echo "Selesai!\n";
