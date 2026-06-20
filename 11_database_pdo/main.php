<?php

// 11 - DATABASE PDO
// Topik: PDO connection, Query Builder, CRUD,
//        prepared statement, transaction,
//        schema migration, model/ORM pattern

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/query.php';
require_once __DIR__ . '/model.php';

$dbPath = sys_get_temp_dir() . '/php_learning.db';
DbConfig::sqlite($dbPath);

// ----- 1. SCHEMA / MIGRATION -----

echo "SCHEMA MIGRATION\n";

$schema = new Schema();

$schema->dropTable('users');
$schema->dropTable('posts');
$schema->dropTable('comments');
$schema->dropTable('categories');

$schema->createTable('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('password');
    $table->integer('age');
    $table->timestamps();
    $table->softDeletes();
    $table->unique('email');
});

$schema->createTable('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug');
    $table->unique('slug');
});

$schema->createTable('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->foreignId('category_id');
    $table->string('title');
    $table->text('content');
    $table->boolean('published');
    $table->timestamps();
});

$schema->createTable('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id');
    $table->foreignId('user_id');
    $table->text('body');
    $table->timestamps();
});

echo "Tables: " . implode(', ', $schema->getTables()) . "\n";

echo "\n";

// ----- 2. RAW SQL (Unsafe, for demo only) -----

echo "RAW SQL\n";

Database::raw("INSERT INTO categories (name, slug) VALUES (?, ?)", ['PHP', 'php']);
Database::raw("INSERT INTO categories (name, slug) VALUES (?, ?)", ['Laravel', 'laravel']);
Database::raw("INSERT INTO categories (name, slug) VALUES (?, ?)", ['JavaScript', 'javascript']);

$categories = Database::fetchAll("SELECT * FROM categories");
echo "Categories:\n";
foreach ($categories as $cat) {
    echo "  [{$cat['id']}] {$cat['name']} ({$cat['slug']})\n";
}

echo "\n";

// ----- 3. QUERY BUILDER INSERT -----

echo "QUERY BUILDER INSERT\n";

$qb = QueryBuilder::table('users');

$budiId = $qb->insert([
    'name' => 'Budi Santoso',
    'email' => 'budi@test.com',
    'password' => password_hash('secret123', PASSWORD_DEFAULT),
    'age' => 25,
]);

$aniId = $qb->insert([
    'name' => 'Ani Wijaya',
    'email' => 'ani@test.com',
    'password' => password_hash('secret456', PASSWORD_DEFAULT),
    'age' => 23,
]);

$citraId = $qb->insert([
    'name' => 'Citra Dewi',
    'email' => 'citra@test.com',
    'password' => password_hash('secret789', PASSWORD_DEFAULT),
    'age' => 28,
]);

echo "Users created: $budiId, $aniId, $citraId\n";

$postQb = QueryBuilder::table('posts');

$posts = [
    ['user_id' => $budiId, 'category_id' => 1, 'title' => 'Belajar PHP Dasar', 'content' => 'PHP adalah bahasa...', 'published' => 1],
    ['user_id' => $budiId, 'category_id' => 2, 'title' => 'Pengenalan Laravel', 'content' => 'Laravel framework...', 'published' => 1],
    ['user_id' => $aniId, 'category_id' => 1, 'title' => 'OOP di PHP', 'content' => 'Object oriented...', 'published' => 1],
    ['user_id' => $aniId, 'category_id' => 3, 'title' => 'JavaScript Modern', 'content' => 'ES6 features...', 'published' => 0],
    ['user_id' => $citraId, 'category_id' => 2, 'title' => 'Eloquent ORM', 'content' => 'Active record...', 'published' => 1],
];

$postQb->insertMultiple($posts);
echo "Posts created: " . count($posts) . "\n";

echo "\n";

// ----- 4. QUERY BUILDER SELECT -----

echo "QUERY BUILDER SELECT\n";

$allPosts = QueryBuilder::table('posts')
    ->select('id', 'title', 'published')
    ->get();

echo "All posts:\n";
foreach ($allPosts as $p) {
    $status = $p['published'] ? 'published' : 'draft';
    echo "  [{$p['id']}] {$p['title']} ($status)\n";
}

echo "\n";

// ----- 5. WHERE CLAUSES -----

echo "WHERE CLAUSES\n";

$activePosts = QueryBuilder::table('posts')
    ->where('published', '=', 1)
    ->get();

echo "Published posts: " . count($activePosts) . "\n";

$budiPosts = QueryBuilder::table('posts')
    ->where('user_id', '=', $budiId)
    ->get();

echo "Budi posts: " . count($budiPosts) . "\n";

$specificPost = QueryBuilder::table('posts')
    ->where('user_id', '=', $budiId)
    ->where('published', '=', 1)
    ->first();

echo "First Budi published: {$specificPost['title']}\n";

$youngUsers = QueryBuilder::table('users')
    ->where('age', '<', 25)
    ->get();

echo "Users under 25: " . count($youngUsers) . "\n";

$usersInRange = QueryBuilder::table('users')
    ->whereBetween('age', 22, 30)
    ->get();

echo "Users age 22-30: " . count($usersInRange) . "\n";

echo "\n";

// ----- 6. ORDER, LIMIT, OFFSET -----

echo "ORDER, LIMIT, OFFSET\n";

$latestPosts = QueryBuilder::table('posts')
    ->orderByDesc('id')
    ->limit(3)
    ->get();

echo "Latest 3 posts:\n";
foreach ($latestPosts as $p) {
    echo "  {$p['id']}. {$p['title']}\n";
}

echo "\n";

// ----- 7. AGGREGATES -----

echo "AGGREGATES\n";

$totalUsers = QueryBuilder::table('users')->count();
echo "Total users: $totalUsers\n";

$avgAge = QueryBuilder::table('users')->avg('age');
echo "Average age: " . round($avgAge, 1) . "\n";

$maxAge = QueryBuilder::table('users')->max('age');
echo "Max age: $maxAge\n";

$minAge = QueryBuilder::table('users')->min('age');
echo "Min age: $minAge\n";

$exists = QueryBuilder::table('users')->where('age', '>', 30)->exists();
echo "User with age > 30: " . ($exists ? 'yes' : 'no') . "\n";

echo "\n";

// ----- 8. JOIN -----

echo "JOIN\n";

$postsWithUsers = QueryBuilder::table('posts')
    ->select('posts.title', 'users.name', 'categories.name as category')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->where('posts.published', '=', 1)
    ->get();

echo "Posts with user and category:\n";
foreach ($postsWithUsers as $row) {
    echo "  {$row['title']} by {$row['name']} [{$row['category']}]\n";
}

echo "\n";

// ----- 9. UPDATE -----

echo "UPDATE\n";

$updated = QueryBuilder::table('users')
    ->where('id', '=', $citraId)
    ->update(['age' => 29]);

echo "Updated: $updated row(s)\n";

$citra = QueryBuilder::table('users')->find($citraId);
echo "Citra age now: {$citra['age']}\n";

echo "\n";

// ----- 10. TRANSACTION -----

echo "TRANSACTION\n";

try {
    Database::transaction(function () use ($budiId) {
        Database::raw("UPDATE users SET age = age + 1 WHERE id = ?", [$budiId]);
        Database::raw("INSERT INTO posts (user_id, category_id, title, content, published) VALUES (?, ?, ?, ?, ?)", [
            $budiId, 1, 'Dari Transaction', 'Dibuat di dalam transaction', 1,
        ]);
        echo "Transaction success\n";
    });
} catch (Throwable $e) {
    echo "Transaction failed: {$e->getMessage()}\n";
}

echo "Query count: " . Database::getQueryCount() . "\n";

echo "\n";

// ----- 11. MODEL (Active Record) -----

echo "MODEL (ACTIVE RECORD)\n";

class User extends Model
{
    protected static string $table = 'users';
    protected array $fillable = ['name', 'email', 'password', 'age'];
}

$allUsers = User::all();
echo "All users via Model:\n";
foreach ($allUsers as $u) {
    echo "  [{$u->id}] {$u->name} ({$u->email})\n";
}

$budi = User::find($budiId);
echo "Find Budi: {$budi->name}, age {$budi->age}\n";

$newUser = User::create([
    'name' => 'Dewi Lestari',
    'email' => 'dewi@test.com',
    'password' => password_hash('test123', PASSWORD_DEFAULT),
    'age' => 27,
]);
echo "Created user: {$newUser->name} (ID: {$newUser->getKey()})\n";

$newUser->name = 'Dewi Lestari Updated';
$saved = $newUser->save();
echo "Saved: " . ($saved ? 'yes' : 'no') . "\n";
echo "Dirty: " . ($newUser->isDirty() ? 'yes' : 'no') . "\n";

$fresh = $newUser->fresh();
echo "Fresh from DB: {$fresh->name}\n";

echo "\n";

// ----- 12. PREPARED STATEMENT (SQL Injection Prevention) -----

echo "SQL INJECTION PREVENTION\n";

function unsafeQuery(string $email): array
{
    $pdo = Database::connect();
    $sql = "SELECT * FROM users WHERE email = '$email'";
    echo "  Unsafe SQL: $sql\n";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function safeQuery(string $email): array
{
    $pdo = Database::connect();
    $sql = "SELECT * FROM users WHERE email = ?";
    echo "  Safe SQL: $sql\n";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    return $stmt->fetchAll();
}

$safeInput = 'budi@test.com';
echo "Safe input:\n";
$result = safeQuery($safeInput);
echo "  Found: " . count($result) . " user(s)\n";

echo "\nUncomment untuk test SQL injection:\n";
echo "  unsafeQuery(\"' OR '1'='1\");\n";

echo "\n";

// ----- 13. PAGINATION HELPERS -----

echo "PAGINATION\n";

function paginate(string $table, int $page = 1, int $perPage = 2): array
{
    $total = QueryBuilder::table($table)->count();
    $lastPage = max(1, (int) ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;

    $items = QueryBuilder::table($table)
        ->orderBy('id')
        ->limit($perPage)
        ->offset($offset)
        ->get();

    return [
        'data' => $items,
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'last_page' => $lastPage,
        'has_more' => $page < $lastPage,
    ];
}

$page1 = paginate('users', 1, 2);
echo "Page {$page1['current_page']} of {$page1['last_page']} (total: {$page1['total']})\n";
foreach ($page1['data'] as $u) {
    echo "  {$u['name']}\n";
}

$page2 = paginate('users', 2, 2);
echo "Page {$page2['current_page']} of {$page2['last_page']} (total: {$page2['total']})\n";
foreach ($page2['data'] as $u) {
    echo "  {$u['name']}\n";
}

echo "\nSelesai belajar database PDO!\n";

// Cleanup
unlink($dbPath);
