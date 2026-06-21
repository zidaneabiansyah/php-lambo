<?php

// 13 - JSON & REST API
// Topik: JSON encode/decode, Router, Response,
//        File-based DB, REST API CRUD, pagination,
//        search, validation

require_once __DIR__ . '/json_handler.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/api.php';

// ----- 1. JSON ENCODE / DECODE -----

echo "JSON ENCODE / DECODE\n";

$data = [
    'name' => 'Budi Santoso',
    'age' => 25,
    'skills' => ['PHP', 'JavaScript', 'Laravel'],
    'address' => [
        'street' => 'Jl. Merdeka No. 1',
        'city' => 'Jakarta',
        'country' => 'Indonesia',
    ],
    'active' => true,
    'score' => null,
];

$encoded = Json::encode($data);
echo "Encoded:\n$encoded\n\n";

$decoded = Json::decode($encoded);
echo "Decoded name: {$decoded['name']}\n";
echo "Decoded city: {$decoded['address']['city']}\n";
echo "Decoded skills: " . implode(', ', $decoded['skills']) . "\n";

$pretty = Json::pretty($data);
echo "\nPretty printed:\n$pretty\n\n";

echo "Is valid JSON: " . (Json::isValid('{"test": 1}') ? 'yes' : 'no') . "\n";
echo "Is valid JSON: " . (Json::isValid('{invalid}') ? 'yes' : 'no') . "\n";

echo "\n";

// ----- 2. JSON FILE STORAGE -----

echo "JSON FILE STORAGE\n";

$jsonPath = sys_get_temp_dir() . '/php_api_data.json';

Json::toFile($jsonPath, ['app' => 'BelajarPHP', 'version' => '1.0']);
$loaded = Json::fromFile($jsonPath);
echo "From file: " . Json::pretty($loaded) . "\n";

echo "\n";

// ----- 3. JSON STREAM -----

echo "JSON STREAM\n";

$stream = new JsonStream();
$stream->write(['event' => 'user_login', 'user_id' => 1]);
$stream->write(['event' => 'page_view', 'path' => '/home']);
$stream->write(['event' => 'purchase', 'amount' => 50000]);

$events = $stream->readAll();
echo "Stream events: " . count($events) . "\n";
foreach ($events as $event) {
    echo "  {$event['event']}\n";
}

echo "\n";

// ----- 4. JSONABLE TRAIT -----

echo "JSONABLE TRAIT\n";

class Product
{
    use Jsonable;

    public function __construct(
        private string $name,
        private float $price,
        private int $stock,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'total_value' => $this->price * $this->stock,
        ];
    }
}

$product = new Product('Laptop', 15000000, 5);
echo $product->toJson() . "\n";

echo "\n";

// ----- 5. FILE DB (CRUD) -----

echo "FILE DB CRUD\n";

$dbPath = sys_get_temp_dir() . '/php_articles.json';
$db = new FileDb($dbPath);

$article1 = $db->create([
    'title' => 'Belajar PHP Dasar',
    'content' => 'Panduan lengkap PHP untuk pemula',
    'author' => 'Budi',
    'status' => 'published',
]);

$article2 = $db->create([
    'title' => 'OOP di PHP',
    'content' => 'Konsep object oriented programming di PHP',
    'author' => 'Ani',
    'status' => 'published',
]);

$article3 = $db->create([
    'title' => 'Database dengan PDO',
    'content' => 'Cara menggunakan PDO untuk akses database',
    'author' => 'Budi',
    'status' => 'draft',
]);

echo "Total articles: " . $db->count() . "\n";

$allArticles = $db->all();
echo "All articles:\n";
foreach ($allArticles as $art) {
    echo "  [{$art['id']}] {$art['title']} by {$art['author']} ({$art['status']})\n";
}

$found = $db->find(2);
echo "\nFind ID 2: {$found['title']}\n";

$updated = $db->update(3, ['status' => 'published', 'title' => 'Database PDO Lengkap']);
echo "Updated: {$updated['title']} ({$updated['status']})\n";

$deleted = $db->delete(1);
echo "Deleted article 1: " . ($deleted ? 'success' : 'failed') . "\n";
echo "Total after delete: " . $db->count() . "\n";

echo "\n";

// ----- 6. PAGINATION & SEARCH -----

echo "PAGINATION & SEARCH\n";

// Add more articles for pagination
for ($i = 1; $i <= 8; $i++) {
    $db->create([
        'title' => "Article $i about PHP",
        'content' => "Content for article $i",
        'author' => $i % 2 === 0 ? 'Ani' : 'Budi',
        'status' => 'published',
    ]);
}

$page1 = $db->paginate(1, 5);
echo "Page {$page1['meta']['current_page']} of {$page1['meta']['last_page']} (total: {$page1['meta']['total']})\n";
foreach ($page1['data'] as $art) {
    echo "  {$art['title']}\n";
}

$page2 = $db->paginate(2, 5);
echo "Page {$page2['meta']['current_page']} of {$page2['meta']['last_page']}\n";
foreach ($page2['data'] as $art) {
    echo "  {$art['title']}\n";
}

$searchResult = $db->search('title', 'article');
echo "\nSearch 'article': " . count($searchResult) . " results\n";

$searchResult2 = $db->search('author', 'ani');
echo "Search 'ani': " . count($searchResult2) . " results\n";

echo "\n";

// ----- 7. ROUTER -----

echo "ROUTER\n";

$router = new Router();

$router->get('/', fn($p) => ['message' => 'API v1.0', 'routes' => $router->availableRoutes()]);

$articleApi = new ApiResource($dbPath);
$articleApi->setRules([
    'title' => ['required', 'min:3'],
    'content' => ['required'],
]);

$router->get('/articles', function ($p) use ($articleApi, $db) {
    return $db->paginate((int) ($p['page'] ?? 1), 5);
});

$router->get('/articles/search', function ($p) use ($db) {
    return ['data' => $db->search('title', $p['q'] ?? '')];
});

$router->get('/articles/{id}', function ($p) use ($db) {
    $article = $db->find((int) $p['id']);
    return $article ?: ['error' => 'Not found'];
});

$router->post('/articles', function ($p) use ($articleApi) {
    $body = ['title' => 'New Article', 'content' => 'Content here', 'author' => 'System'];
    $result = $articleApi->store($body);
    return $result;
});

$router->put('/articles/{id}', function ($p) use ($db) {
    $updated = $db->update((int) $p['id'], ['title' => 'Updated Title', 'updated_at' => date('c')]);
    return $updated ?: ['error' => 'Not found'];
});

$router->delete('/articles/{id}', function ($p) use ($db) {
    return ['deleted' => $db->delete((int) $p['id'])];
});

echo $router->list();

echo "\nRoute matching:\n";
$tests = [
    ['GET', '/'],
    ['GET', '/articles'],
    ['GET', '/articles/2'],
    ['GET', '/articles/search?q=php'],
    ['POST', '/articles'],
    ['PUT', '/articles/3'],
    ['DELETE', '/articles/5'],
    ['GET', '/nonexistent'],
];

foreach ($tests as [$method, $uri]) {
    $result = $router->dispatch($method, $uri);
    $output = is_array($result) ? Json::encode($result) : (string) $result;
    echo "  $method $uri -> " . substr($output, 0, 80) . "...\n";
}

echo "\n";

// ----- 8. RESPONSE -----

echo "RESPONSE\n";

$responses = [
    Response::ok(['user' => 'Budi', 'role' => 'admin']),
    Response::created(['id' => 1, 'name' => 'New Item']),
    Response::noContent(),
    Response::badRequest('Invalid email format'),
    Response::unauthorized('Token expired'),
    Response::forbidden('Insufficient permissions'),
    Response::notFound('User not found'),
    Response::error('Something broke'),
];

foreach ($responses as $resp) {
    echo "Status {$resp->getStatusCode()}: {$resp->getBody()}\n";
}

echo "\n";

// ----- 9. FILE DB CLEANUP -----

unlink($dbPath);
unlink($jsonPath);

echo "Temp files cleaned\n";
echo "\nSelesai belajar JSON & REST API!\n";
