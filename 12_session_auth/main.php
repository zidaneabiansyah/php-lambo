<?php

ob_start();

// 12 - SESSION & AUTHENTICATION
// Topik: Session management, login/logout,
//        password hashing, middleware pipeline,
//        role-based access, CSRF, throttling

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/middleware.php';

Session::start();

// Simulated user database
$users = [
    [
        'id' => 1,
        'name' => 'Budi Santoso',
        'email' => 'budi@test.com',
        'password' => Password::hash('Admin123!'),
        'role' => 'admin',
    ],
    [
        'id' => 2,
        'name' => 'Ani Wijaya',
        'email' => 'ani@test.com',
        'password' => Password::hash('User1234!'),
        'role' => 'user',
    ],
];

// ----- 1. SESSION BASICS -----

echo "SESSION BASICS\n";

echo "Session ID: " . Session::id() . "\n";

Session::set('username', 'budi123');
Session::set('theme', 'dark');
Session::set('language', 'id');

echo "Username: " . Session::get('username') . "\n";
echo "Theme: " . Session::get('theme') . "\n";
echo "Has language: " . (Session::has('language') ? 'yes' : 'no') . "\n";

Session::increment('visit_count');
Session::increment('visit_count');
echo "Visit count: " . Session::get('visit_count') . "\n";

echo "\n";

// ----- 2. FLASH MESSAGES -----

echo "FLASH MESSAGES\n";

Session::flash('success', 'Data berhasil disimpan!');
Session::flash('error', 'Terjadi kesalahan.');

Session::ageFlashData();

echo "Flash success: " . Session::flash('success') . "\n";
echo "Flash error: " . Session::flash('error') . "\n";

Session::flash('info', 'Update tersedia.');
Session::ageFlashData();
echo "Has flash info: " . (Session::hasFlash('info') ? 'yes' : 'no') . "\n";

echo "\n";

// ----- 3. OLD INPUT -----

echo "OLD INPUT\n";

Session::setOld([
    'name' => 'Budi',
    'email' => 'budi@test.com',
]);

echo "Old name: " . Session::old('name') . "\n";
echo "Old email: " . Session::old('email') . "\n";
echo "Old password: " . (Session::old('password') ?: '(kosong)') . "\n";

echo "\n";

// ----- 4. PASSWORD HASHING -----

echo "PASSWORD HASHING\n";

$plainPassword = 'Admin123!';
$hash = Password::hash($plainPassword);
echo "Hash: $hash\n";
echo "Length: " . strlen($hash) . "\n";

echo "Verify correct: " . (Password::verify($plainPassword, $hash) ? 'PASS' : 'FAIL') . "\n";
echo "Verify wrong: " . (Password::verify('wrongpass', $hash) ? 'PASS' : 'FAIL') . "\n";
echo "Needs rehash: " . (Password::needsRehash($hash) ? 'yes' : 'no') . "\n";

$validationErrors = Password::validate('short');
echo "Validation errors: " . (empty($validationErrors) ? 'none' : implode(', ', $validationErrors)) . "\n";

$generated = Password::generate(12);
echo "Generated password: $generated\n";

echo "\n";

// ----- 5. AUTH LOGIN -----

echo "AUTH LOGIN\n";

echo "Guest? " . (Auth::guest() ? 'yes' : 'no') . "\n";

$loginResult = Auth::attempt('budi@test.com', 'Admin123!', $users);
echo "Login success: " . ($loginResult ? 'PASS' : 'FAIL') . "\n";

echo "Check: " . (Auth::check() ? 'authenticated' : 'guest') . "\n";
echo "User ID: " . Auth::id() . "\n";
echo "User name: " . (Auth::user()['name'] ?? 'N/A') . "\n";
echo "User role: " . Auth::role() . "\n";
echo "Is admin: " . (Auth::isAdmin() ? 'yes' : 'no') . "\n";

echo "Can read: " . (Auth::can('read') ? 'yes' : 'no') . "\n";
echo "Can delete: " . (Auth::can('delete') ? 'yes' : 'no') . "\n";

// Test failed login
$failed = Auth::attempt('budi@test.com', 'wrongpass', $users);
echo "Failed login: " . ($failed ? 'PASS (unexpected)' : 'FAIL (expected)') . "\n";

echo "\n";

// ----- 6. AUTH LOGOUT -----

echo "AUTH LOGOUT\n";

Auth::logout();
echo "After logout, check: " . (Auth::check() ? 'authenticated' : 'guest') . "\n";
echo "Guest: " . (Auth::guest() ? 'yes' : 'no') . "\n";

echo "\n";

// ----- 7. MIDDLEWARE PIPELINE -----

echo "MIDDLEWARE PIPELINE\n";

function dashboard(): string
{
    $user = Auth::user();
    return "Welcome to dashboard, {$user['name']}!\n";
}

function adminPanel(): string
{
    return "Admin panel accessed\n";
}

$pipeline = Middleware::pipe([
    new LogMiddleware('Dashboard'),
    new AuthMiddleware(),
], fn() => dashboard());

echo "--- Without login ---\n";
$result = $pipeline();

echo "\n--- With login ---\n";
Auth::attempt('budi@test.com', 'Admin123!', $users);
$result = $pipeline();
if ($result) echo "  $result";

echo "\n--- Admin panel with role check ---\n";
$adminPipeline = Middleware::pipe([
    new LogMiddleware('AdminPanel'),
    new AuthMiddleware(),
    new RoleMiddleware('admin'),
], fn() => adminPanel());

$result = $adminPipeline();
if ($result) echo "  $result";

echo "\n--- Logout & try admin ---\n";
Auth::logout();
$result = $adminPipeline();

echo "\n";

// ----- 8. CSRF TOKEN -----

echo "CSRF TOKEN\n";

$token = Session::token();
echo "CSRF token: $token\n";

echo "Verify valid: " . (Session::verifyToken($token) ? 'PASS' : 'FAIL') . "\n";
echo "Verify invalid: " . (Session::verifyToken('fake') ? 'PASS' : 'FAIL') . "\n";

echo "\n";

// ----- 9. COMPLETE AUTH FLOW -----

echo "COMPLETE AUTH FLOW\n";

function registerUser(array $data, array &$users): array
{
    $errors = [];

    if (empty($data['name'])) $errors[] = 'Name required';
    if (empty($data['email'])) $errors[] = 'Email required';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';

    $pwErrors = Password::validate($data['password'] ?? '');
    if (!empty($pwErrors)) {
        $errors = array_merge($errors, $pwErrors);
    }

    if (!empty($errors)) {
        Session::setOld($data);
        return ['success' => false, 'errors' => $errors];
    }

    $user = [
        'id' => count($users) + 1,
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Password::hash($data['password']),
        'role' => 'user',
    ];

    $users[] = $user;
    Auth::loginById($user['id'], $users);
    Session::flash('success', 'Registration successful!');

    return ['success' => true, 'user' => Auth::user()];
}

function changePassword(string $currentPassword, string $newPassword, array $users): array
{
    if (!Auth::check()) {
        return ['success' => false, 'error' => 'Not authenticated'];
    }

    $userId = Auth::id();
    $user = null;
    foreach ($users as $u) {
        if ($u['id'] === $userId) {
            $user = $u;
            break;
        }
    }

    if (!$user || !Password::verify($currentPassword, $user['password'])) {
        return ['success' => false, 'error' => 'Current password is wrong'];
    }

    $pwErrors = Password::validate($newPassword);
    if (!empty($pwErrors)) {
        return ['success' => false, 'errors' => $pwErrors];
    }

    $user['password'] = Password::hash($newPassword);
    Session::flash('success', 'Password changed!');
    return ['success' => true];
}

Auth::logout();

$registerResult = registerUser([
    'name' => 'Citra Dewi',
    'email' => 'citra@test.com',
    'password' => 'Citra123!',
], $users);

if ($registerResult['success']) {
    echo "Register OK: {$registerResult['user']['name']} ({$registerResult['user']['role']})\n";
    Session::ageFlashData();
    echo "Flash: " . Session::flash('success') . "\n";
}

$changeResult = changePassword('Citra123!', 'NewPass456!', $users);
if ($changeResult['success']) {
    echo "Password changed\n";
    Session::ageFlashData();
    echo "Flash: " . Session::flash('success') . "\n";
}

echo "\n";

// ----- 10. SESSION CLEAR -----

echo "SESSION STATE\n";

echo "Session data before clear:\n";
foreach (Session::all() as $key => $value) {
    $display = is_array($value) ? json_encode($value) : $value;
    echo "  $key: " . substr((string) $display, 0, 60) . "\n";
}

echo "\nSelesai belajar session & authentication!\n";

ob_end_flush();
