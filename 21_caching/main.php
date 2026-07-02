<?php

// ============================================
// 21 - CACHING
// ============================================
// Topik: Cache interface, File cache, Array cache,
//        TTL, Cache tags, Cache-aside pattern,
//        Multiple drivers, Cache decorator
// ============================================

echo "==========================================\n";
echo "  CACHING\n";
echo "==========================================\n\n";

// ============================================
// BAGIAN A: CACHE INTERFACE (PSR-16-like)
// ============================================

interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 300): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function has(string $key): bool;
}

// ============================================
// BAGIAN B: ARRAY CACHE (In-Memory)
// ============================================

echo "--- 1. ARRAY CACHE (In-Memory) ---\n\n";

class ArrayCache implements CacheInterface
{
    private array $store = [];
    private array $ttl = [];

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        if (time() > $this->ttl[$key]) {
            $this->delete($key);
            return null;
        }

        return $this->store[$key];
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        $this->store[$key] = $value;
        $this->ttl[$key] = time() + $ttl;
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->store[$key], $this->ttl[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->store = [];
        $this->ttl = [];
        return true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->store);
    }

    public function count(): int
    {
        return count($this->store);
    }

    public function keys(): array
    {
        return array_keys($this->store);
    }
}

$cache = new ArrayCache();

// Set data
$cache->set('user:1', ['id' => 1, 'name' => 'Budi', 'email' => 'budi@example.com']);
$cache->set('user:2', ['id' => 2, 'name' => 'Andi', 'email' => 'andi@example.com']);
$cache->set('config:app', ['debug' => true, 'timezone' => 'Asia/Jakarta'], 60);

// Get data
$user = $cache->get('user:1');
echo "  User 1: " . $user['name'] . " (" . $user['email'] . ")\n";

$config = $cache->get('config:app');
echo "  Config: " . json_encode($config) . "\n";

// Check existence
echo "  Has user:1: " . ($cache->has('user:1') ? 'Ya' : 'Tidak') . "\n";
echo "  Has user:99: " . ($cache->has('user:99') ? 'Ya' : 'Tidak') . "\n";
echo "  Cache count: " . $cache->count() . "\n";
echo "  Keys: " . implode(', ', $cache->keys()) . "\n\n";


echo "--- 2. FILE CACHE ---\n\n";

class FileCache implements CacheInterface
{
    private string $directory;
    private int $defaultTtl;

    public function __construct(string $directory = null, int $defaultTtl = 300)
    {
        $this->directory = $directory ?? sys_get_temp_dir() . '/php_cache';
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    private function getFilePath(string $key): string
    {
        return $this->directory . '/' . md5($key) . '.cache';
    }

    public function get(string $key): mixed
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $data = unserialize(file_get_contents($file));

        if (time() > $data['expires']) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time(),
        ];

        return file_put_contents($this->getFilePath($key), serialize($data)) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->directory . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function getMultiple(array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        return $results;
    }

    public function setMultiple(array $values, int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }
}

$fileCache = new FileCache();

$fileCache->set('product:1', ['name' => 'Laptop', 'price' => 15000000]);
$fileCache->set('product:2', ['name' => 'Mouse', 'price' => 150000]);
$fileCache->set('product:3', ['name' => 'Keyboard', 'price' => 750000], 600);

$product = $fileCache->get('product:1');
echo "  Product 1: {$product['name']} - Rp " . number_format($product['price']) . "\n";
echo "  Has product:2: " . ($fileCache->has('product:2') ? 'Ya' : 'Tidak') . "\n\n";


// ============================================
// BAGIAN C: TTL (Time-To-Live)
// ============================================

echo "--- 3. TTL (Time-To-Live) ---\n\n";

class TTLCache implements CacheInterface
{
    private array $store = [];
    private array $expires = [];
    private int $defaultTtl;

    public function __construct(int $defaultTtl = 300)
    {
        $this->defaultTtl = $defaultTtl;
    }

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        $this->cleanup($key);
        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $this->store[$key] = $value;
        $this->expires[$key] = $ttl > 0 ? time() + $ttl : PHP_INT_MAX;
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->store[$key], $this->expires[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->store = [];
        $this->expires = [];
        return true;
    }

    public function has(string $key): bool
    {
        $this->cleanup($key);
        return array_key_exists($key, $this->store);
    }

    private function cleanup(string $key): void
    {
        if (isset($this->expires[$key]) && time() > $this->expires[$key]) {
            $this->delete($key);
        }
    }

    public function getTtl(string $key): int|false
    {
        if (!$this->has($key)) {
            return false;
        }
        return $this->expires[$key] - time();
    }

    public function touch(string $key, int $ttl = null): bool
    {
        if (!$this->has($key)) {
            return false;
        }
        $ttl = $ttl ?? $this->defaultTtl;
        $this->expires[$key] = time() + $ttl;
        return true;
    }
}

$ttlCache = new TTLCache();

// Set dengan TTL berbeda
$ttlCache->set('short', 'Expires in 10 seconds', 10);
$ttlCache->set('medium', 'Expires in 60 seconds', 60);
$ttlCache->set('long', 'Expires in 300 seconds', 300);
$ttlCache->set('forever', 'Never expires (PHP_INT_MAX)', 0);

echo "  short TTL: {$ttlCache->getTtl('short')}s\n";
echo "  medium TTL: {$ttlCache->getTtl('medium')}s\n";
echo "  long TTL: {$ttlCache->getTtl('long')}s\n";
echo "  forever TTL: " . ($ttlCache->getTtl('forever') === PHP_INT_MAX ? '∞' : $ttlCache->getTtl('forever')) . "\n\n";

// Touch (extend TTL)
$ttlCache->touch('short', 120);
echo "  After touch(short, 120): {$ttlCache->getTtl('short')}s\n\n";


// ============================================
// BAGIAN D: CACHE TAGS & INVALIDATION
// ============================================

echo "--- 4. CACHE TAGS ---\n\n";

class TagCache implements CacheInterface
{
    private CacheInterface $cache;
    private array $tagMap = []; // tag -> [keys]

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function setWithTags(string $key, mixed $value, array $tags, int $ttl = 300): bool
    {
        $result = $this->cache->set($key, $value, $ttl);

        if ($result) {
            foreach ($tags as $tag) {
                if (!isset($this->tagMap[$tag])) {
                    $this->tagMap[$tag] = [];
                }
                $this->tagMap[$tag][] = $key;
            }
        }

        return $result;
    }

    public function delete(string $key): bool
    {
        // Hapus dari tag map
        foreach ($this->tagMap as $tag => $keys) {
            $this->tagMap[$tag] = array_filter($keys, fn($k) => $k !== $key);
        }
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        $this->tagMap = [];
        return $this->cache->clear();
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    public function invalidateTag(string $tag): int
    {
        $keys = $this->tagMap[$tag] ?? [];
        $count = 0;

        foreach ($keys as $key) {
            $this->cache->delete($key);
            $count++;
        }

        unset($this->tagMap[$tag]);
        return $count;
    }

    public function invalidateTags(array $tags): int
    {
        $total = 0;
        foreach ($tags as $tag) {
            $total += $this->invalidateTag($tag);
        }
        return $total;
    }
}

$tagCache = new TagCache(new ArrayCache());

// Set data dengan tags
$tagCache->setWithTags('post:1', ['title' => 'Post 1', 'content' => 'Content 1'], ['posts', 'published']);
$tagCache->setWithTags('post:2', ['title' => 'Post 2', 'content' => 'Content 2'], ['posts', 'draft']);
$tagCache->setWithTags('user:1', ['name' => 'Budi'], ['users', 'admin']);
$tagCache->setWithTags('user:2', ['name' => 'Andi'], ['users', 'editor']);

echo "  Post 1: " . $tagCache->get('post:1')['title'] . "\n";
echo "  User 1: " . $tagCache->get('user:1')['name'] . "\n\n";

// Invalidate semua posts
$deleted = $tagCache->invalidateTag('posts');
echo "  Invalidated $deleted posts\n";
echo "  Post 1 after invalidation: " . ($tagCache->get('post:1') === null ? 'NULL' : 'EXISTS') . "\n";
echo "  User 1 still exists: " . ($tagCache->get('user:1')['name'] ?? 'NULL') . "\n\n";


// ============================================
// BAGIAN E: CACHE-ASIDE PATTERN
// ============================================

echo "--- 5. CACHE-ASIDE PATTERN ---\n\n";

class UserRepositoryCached
{
    private CacheInterface $cache;

    // Simulasi database
    private array $db = [
        1 => ['id' => 1, 'name' => 'Budi', 'email' => 'budi@example.com'],
        2 => ['id' => 2, 'name' => 'Andi', 'email' => 'andi@example.com'],
        3 => ['id' => 3, 'name' => 'Citra', 'email' => 'citra@example.com'],
    ];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function find(int $id): ?array
    {
        $cacheKey = "user:$id";

        // 1. Check cache dulu
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            echo "    [CACHE HIT] User $id\n";
            return $cached;
        }

        // 2. Cache miss - fetch dari DB
        echo "    [CACHE MISS] User $id - fetching from DB\n";
        $user = $this->db[$id] ?? null;

        // 3. Simpan ke cache
        if ($user !== null) {
            $this->cache->set($cacheKey, $user, 300);
        }

        return $user;
    }

    public function update(int $id, array $data): ?array
    {
        if (!isset($this->db[$id])) {
            return null;
        }

        // Update DB
        $this->db[$id] = array_merge($this->db[$id], $data);

        // Invalidate cache
        $this->cache->delete("user:$id");
        echo "    [CACHE INVALIDATED] User $id\n";

        return $this->db[$id];
    }

    public function findAll(): array
    {
        $cacheKey = 'users:all';

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            echo "    [CACHE HIT] All users\n";
            return $cached;
        }

        echo "    [CACHE MISS] All users - fetching from DB\n";
        $users = array_values($this->db);
        $this->cache->set($cacheKey, $users, 600);

        return $users;
    }
}

$repo = new UserRepositoryCached($tagCache);

echo "  First request:\n";
$user = $repo->find(1);
echo "  Result: {$user['name']}\n\n";

echo "  Second request (cached):\n";
$user = $repo->find(1);
echo "  Result: {$user['name']}\n\n";

echo "  Update user:\n";
$repo->update(1, ['name' => 'Budi Santoso']);
$user = $repo->find(1);
echo "  Result: {$user['name']}\n\n";


// ============================================
// BAGIAN F: MULTIPLE CACHE DRIVERS
// ============================================

echo "--- 6. MULTIPLE CACHE DRIVERS ---\n\n";

class CacheManager
{
    private array $drivers = [];
    private string $defaultDriver;

    public function __construct(string $defaultDriver = 'array')
    {
        $this->defaultDriver = $defaultDriver;
    }

    public function extend(string $name, CacheInterface $driver): self
    {
        $this->drivers[$name] = $driver;
        return $this;
    }

    public function driver(string $name = null): CacheInterface
    {
        $name = $name ?? $this->defaultDriver;

        if (!isset($this->drivers[$name])) {
            throw new \RuntimeException("Cache driver '$name' not registered");
        }

        return $this->drivers[$name];
    }

    public function getDriverNames(): array
    {
        return array_keys($this->drivers);
    }
}

$manager = new CacheManager('array');
$manager->extend('array', new ArrayCache());
$manager->extend('file', new FileCache());

echo "  Available drivers: " . implode(', ', $manager->getDriverNames()) . "\n";

// Gunakan driver tertentu
$arrayDriver = $manager->driver('array');
$arrayDriver->set('test', 'from array driver');
echo "  Array driver: " . $arrayDriver->get('test') . "\n";

$fileDriver = $manager->driver('file');
$fileDriver->set('test', 'from file driver');
echo "  File driver: " . $fileDriver->get('test') . "\n\n";


// ============================================
// BAGIAN G: CACHE DECORATOR (Logging)
// ============================================

echo "--- 7. CACHE DECORATOR (Logging) ---\n\n";

class LoggingCache implements CacheInterface
{
    private CacheInterface $cache;
    private array $logs = [];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key): mixed
    {
        $start = microtime(true);
        $result = $this->cache->get($key);
        $duration = (microtime(true) - $start) * 1000;

        $hit = $result !== null;
        $this->logs[] = "GET $key - " . ($hit ? 'HIT' : 'MISS') . " (" . round($duration, 2) . "ms)";

        return $result;
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        $start = microtime(true);
        $result = $this->cache->set($key, $value, $ttl);
        $duration = (microtime(true) - $start) * 1000;

        $this->logs[] = "SET $key - " . ($result ? 'OK' : 'FAIL') . " (TTL: {$ttl}s, " . round($duration, 2) . "ms)";

        return $result;
    }

    public function delete(string $key): bool
    {
        $result = $this->cache->delete($key);
        $this->logs[] = "DELETE $key - " . ($result ? 'OK' : 'FAIL');
        return $result;
    }

    public function clear(): bool
    {
        $result = $this->cache->clear();
        $this->logs[] = "CLEAR - " . ($result ? 'OK' : 'FAIL');
        return $result;
    }

    public function has(string $key): bool
    {
        $result = $this->cache->has($key);
        $this->logs[] = "HAS $key - " . ($result ? 'YES' : 'NO');
        return $result;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function clearLogs(): void
    {
        $this->logs = [];
    }

    public function getStats(): array
    {
        $hits = 0;
        $misses = 0;

        foreach ($this->logs as $log) {
            if (str_contains($log, 'GET') && str_contains($log, 'HIT')) {
                $hits++;
            } elseif (str_contains($log, 'GET') && str_contains($log, 'MISS')) {
                $misses++;
            }
        }

        return [
            'total_operations' => count($this->logs),
            'hits' => $hits,
            'misses' => $misses,
            'hit_rate' => $hits + $misses > 0 ? round($hits / ($hits + $misses) * 100, 1) . '%' : 'N/A',
        ];
    }
}

$loggingCache = new LoggingCache(new ArrayCache());

// Operasi cache
$loggingCache->set('user:1', 'Budi', 300);
$loggingCache->set('user:2', 'Andi', 300);
$loggingCache->get('user:1');
$loggingCache->get('user:1');
$loggingCache->get('user:99');
$loggingCache->delete('user:2');
$loggingCache->get('user:2');

echo "  Cache logs:\n";
foreach ($loggingCache->getLogs() as $log) {
    echo "    $log\n";
}

echo "\n  Stats:\n";
$stats = $loggingCache->getStats();
echo "    Total operations: {$stats['total_operations']}\n";
echo "    Hits: {$stats['hits']}\n";
echo "    Misses: {$stats['misses']}\n";
echo "    Hit rate: {$stats['hit_rate']}\n\n";


// ============================================
// BAGIAN H: CACHE WARMING
// ============================================

echo "--- 8. CACHE WARMING ---\n\n";

class CacheWarmer
{
    private CacheInterface $cache;
    private array $warmers = [];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function register(string $key, callable $callback, int $ttl = 300): self
    {
        $this->warmers[$key] = [
            'callback' => $callback,
            'ttl' => $ttl,
        ];
        return $this;
    }

    public function warm(): array
    {
        $results = [];
        foreach ($this->warmers as $key => $warmer) {
            $start = microtime(true);
            $data = ($warmer['callback'])();
            $this->cache->set($key, $data, $warmer['ttl']);
            $duration = (microtime(true) - $start) * 1000;
            $results[$key] = round($duration, 2) . 'ms';
        }
        return $results;
    }

    public function warmKey(string $key): bool
    {
        if (!isset($this->warmers[$key])) {
            return false;
        }

        $warmer = $this->warmers[$key];
        $data = ($warmer['callback'])();
        return $this->cache->set($key, $data, $warmer['ttl']);
    }
}

$warmer = new CacheWarmer($tagCache);

// Register cache warmers
$warmer->register('popular:products', function () {
    echo "    Warming: Fetching popular products...\n";
    return [
        ['id' => 1, 'name' => 'Laptop'],
        ['id' => 2, 'name' => 'Smartphone'],
    ];
}, 600);

$warmer->register('stats:visitors', function () {
    echo "    Warming: Counting visitors...\n";
    return ['today' => 1500, 'week' => 10500];
}, 300);

echo "  Warming cache:\n";
$results = $warmer->warm();
foreach ($results as $key => $duration) {
    echo "    $key warmed in $duration\n";
}
echo "\n";

echo "  Cached popular products: " . $tagCache->get('popular:products')[0]['name'] . "\n\n";


// ============================================
// BAGIAN I: CACHE INVALIDATION
// ============================================

echo "--- 9. CACHE INVALIDATION STRATEGIES ---\n\n";

class InvalidationCache implements CacheInterface
{
    private CacheInterface $cache;
    private array $dependencies = [];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    // Strategy 1: Time-based invalidation
    public function setWithExpiry(string $key, mixed $value, int $ttl): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    // Strategy 2: Event-based invalidation
    public function onEvent(string $event, array $keys): self
    {
        $this->dependencies[$event] = $keys;
        return $this;
    }

    public function triggerEvent(string $event): int
    {
        $count = 0;
        foreach ($this->dependencies[$event] ?? [] as $key) {
            $this->cache->delete($key);
            $count++;
        }
        return $count;
    }

    // Strategy 3: Version-based invalidation
    public function setVersioned(string $key, mixed $value, string $version, int $ttl = 300): bool
    {
        return $this->cache->set("$key:v$version", $value, $ttl);
    }

    public function getVersioned(string $key, string $version): mixed
    {
        return $this->cache->get("$key:v$version");
    }

    public function invalidateVersion(string $key, string $oldVersion, string $newVersion): void
    {
        $this->cache->delete("$key:v$oldVersion");
    }
}

$invalidator = new InvalidationCache(new ArrayCache());

// Event-based invalidation
$invalidator->onEvent('post.updated', ['posts:all', 'posts:recent']);
$invalidator->onEvent('user.updated', ['users:all', 'user:profile']);

$invalidator->set('posts:all', ['Post 1', 'Post 2'], 300);
$invalidator->set('posts:recent', ['Post 2'], 300);
$invalidator->set('users:all', ['User 1'], 300);

echo "  Before event: posts:all exists = " . ($invalidator->has('posts:all') ? 'Ya' : 'Tidak') . "\n";

$count = $invalidator->triggerEvent('post.updated');
echo "  After post.updated event: invalidated $count keys\n";
echo "  posts:all exists = " . ($invalidator->has('posts:all') ? 'Ya' : 'Tidak') . "\n";
echo "  users:all exists = " . ($invalidator->has('users:all') ? 'Ya' : 'Tidak') . "\n\n";


// ============================================
// BAGIAN J: PRACTICAL EXAMPLE
// ============================================

echo "--- 10. PRACTICAL: CACHING DB QUERY RESULTS ---\n\n";

class ProductRepository
{
    private LoggingCache $cache;

    // Simulasi database yang lambat
    private array $db = [
        ['id' => 1, 'name' => 'Laptop ASUS', 'price' => 15000000, 'category' => 'electronics'],
        ['id' => 2, 'name' => 'iPhone 15', 'price' => 20000000, 'category' => 'electronics'],
        ['id' => 3, 'name' => 'Buku PHP', 'price' => 150000, 'category' => 'books'],
        ['id' => 4, 'name' => 'Keyboard Mech', 'price' => 750000, 'category' => 'electronics'],
        ['id' => 5, 'name' => 'Buku Laravel', 'price' => 200000, 'category' => 'books'],
    ];

    public function __construct(LoggingCache $cache)
    {
        $this->cache = $cache;
    }

    public function findAll(): array
    {
        $cacheKey = 'products:all';
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        // Simulasi slow query
        usleep(100000); // 100ms
        $result = $this->db;
        $this->cache->set($cacheKey, $result, 600);

        return $result;
    }

    public function findByCategory(string $category): array
    {
        $cacheKey = "products:category:$category";
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        usleep(100000); // 100ms
        $result = array_filter($this->db, fn($p) => $p['category'] === $category);
        $this->cache->set($cacheKey, array_values($result), 300);

        return array_values($result);
    }

    public function find(int $id): ?array
    {
        $cacheKey = "product:$id";
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        usleep(50000); // 50ms
        foreach ($this->db as $product) {
            if ($product['id'] === $id) {
                $this->cache->set($cacheKey, $product, 300);
                return $product;
            }
        }

        return null;
    }
}

$productRepo = new ProductRepository($loggingCache);
$loggingCache->clearLogs();

// Query pertama (slow - cache miss)
echo "  First query (cache miss):\n";
$products = $productRepo->findAll();
echo "    Found " . count($products) . " products\n";

// Query kedua (fast - cache hit)
echo "  Second query (cache hit):\n";
$products = $productRepo->findAll();
echo "    Found " . count($products) . " products\n";

// Category query
$electronics = $productRepo->findByCategory('electronics');
echo "  Electronics: " . count($electronics) . " products\n";

// Cache stats
$stats = $loggingCache->getStats();
echo "\n  Performance stats:\n";
echo "    Hits: {$stats['hits']}\n";
echo "    Misses: {$stats['misses']}\n";
echo "    Hit rate: {$stats['hit_rate']}\n\n";


echo "==========================================\n";
echo "  RINGKASAN\n";
echo "==========================================\n";
echo "\n";
echo "CACHE PATTERNS:\n";
echo "  - Array Cache: In-memory, fastest, non-persistent\n";
echo "  - File Cache: Persistent, good for single server\n";
echo "  - TTL: Time-based expiration\n";
echo "  - Tags: Group invalidation\n";
echo "  - Cache-Aside: Check cache → miss → fetch → store\n";
echo "  - Cache Warming: Pre-populate cache\n";
echo "  - Cache Decorator: Add logging/metrics\n";
echo "\n";
echo "INVALIDATION STRATEGIES:\n";
echo "  - Time-based: TTL expiration\n";
echo "  - Event-based: Invalidate on events\n";
echo "  - Version-based: Cache key versioning\n";
echo "\n";

echo "Selesai!\n";
