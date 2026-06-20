<?php

// 10 - FORM HANDLING
// Topik: Validasi form, sanitasi input, CSRF
//        protection, flash messages, file upload

require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/upload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----- 1. BASIC VALIDATION -----

echo "BASIC VALIDATION\n";

$validator = new Validator();

$data = [
    'name' => '  Budi  ',
    'email' => 'budi@test',
    'age' => '25',
    'password' => 'short',
    'password_confirmation' => 'different',
    'website' => 'not-a-url',
    'bio' => '',
];

// Trim dulu
$data = Sanitizer::trim($data, ['name', 'email', 'bio']);

$rules = [
    'name' => 'required|alpha_space|min:3|max:50',
    'email' => 'required|email',
    'age' => 'required|numeric|min_value:17|max_value:100',
    'password' => 'required|min:8|confirmed',
    'website' => 'url',
    'bio' => 'max:500',
];

$errors = $validator->validate($data, $rules);

if ($validator->passes()) {
    echo "Semua validasi lolos\n";
} else {
    echo "Error validasi:\n";
    foreach ($validator->errors() as $field => $msgs) {
        foreach ($msgs as $msg) {
            echo "  [$field] $msg\n";
        }
    }
}

echo "\n";

// ----- 2. CUSTOM ERROR MESSAGES -----

echo "CUSTOM MESSAGES\n";

$customMsg = [
    'name.required' => 'Nama lengkap harus diisi ya!',
    'email.required' => 'Email wajib diisi.',
    'email.email' => 'Format email tidak valid.',
];

$data2 = ['name' => '', 'email' => 'not-an-email'];
$errors2 = $validator->validate($data2, [
    'name' => 'required|min:3',
    'email' => 'required|email',
], $customMsg);

foreach ($validator->allErrors() as $msg) {
    echo "  $msg\n";
}

echo "\n";

// ----- 3. SANITIZATION -----

echo "SANITIZATION\n";

$dirtyInput = [
    'name' => '  <script>alert("xss")</script>Budi  ',
    'email' => '  BUDI@TEST.COM  ',
    'comment' => '<b>Nice post!</b> <a href="http://evil.com">click</a>',
];

$clean = Sanitizer::stripTags($dirtyInput, ['name', 'comment']);
$clean = Sanitizer::trim($clean);

echo "Original name: " . $dirtyInput['name'] . "\n";
echo "Cleaned name: " . $clean['name'] . "\n";
echo "Cleaned email: " . $clean['email'] . "\n";
echo "Cleaned comment: " . $clean['comment'] . "\n";

// XSS safe output
$safeComment = htmlspecialchars($clean['comment'], ENT_QUOTES);
echo "Safe output: $safeComment\n";

echo "\n";

// ----- 4. FORM DATA HELPER -----

echo "FORM DATA HELPER\n";

$form = new FormData([
    'username' => 'budi123',
    'email' => 'budi@test.com',
    'password' => 'secret',
    'remember' => true,
]);

echo "Username: " . $form->get('username') . "\n";
echo "Has remember: " . ($form->has('remember') ? 'yes' : 'no') . "\n";

$safe = $form->except('password');
echo "Without password: " . json_encode($safe) . "\n";

$subset = $form->only('username', 'email');
echo "Only username+email: " . json_encode($subset) . "\n";

echo "\n";

// ----- 5. CSRF PROTECTION -----

echo "CSRF PROTECTION\n";

$token = CsrfProtection::generate();
echo "CSRF token: $token\n";

$validToken = $token;
echo "Verify valid: " . (CsrfProtection::verify($validToken) ? 'PASS' : 'FAIL') . "\n";

$token2 = CsrfProtection::generate();
echo "Verify invalid: " . (CsrfProtection::verify('fake-token') ? 'PASS' : 'FAIL') . "\n";

echo "\n";

// ----- 6. FLASH MESSAGES -----

echo "FLASH MESSAGES\n";

FlashMessage::success('Data berhasil disimpan!');
FlashMessage::error('Email sudah terdaftar.');
FlashMessage::warning('Password akan expired dalam 7 hari.');
FlashMessage::info('Update terbaru tersedia.');

echo "Flash messages tersimpan:\n";
$allFlash = FlashMessage::all();
foreach ($allFlash as $type => $msgs) {
    foreach ($msgs as $msg) {
        echo "  [$type] $msg\n";
    }
}

// Coba ambil lagi (harus kosong karena flash)
$afterRead = FlashMessage::all();
echo "Setelah dibaca: " . (empty($afterRead) ? 'kosong (flash behavior OK)' : 'error') . "\n";

echo "\n";

// ----- 7. OLD INPUT -----

echo "OLD INPUT\n";

OldInput::repopulate([
    'username' => 'budi',
    'email' => 'budi@test.com',
]);

echo "Old username: " . OldInput::get('username') . "\n";
echo "Old email: " . OldInput::get('email') . "\n";
echo "Old password: " . (OldInput::get('password', '(not set)')) . "\n";

OldInput::clear();
echo "After clear: " . (OldInput::get('username', 'kosong')) . "\n";

echo "\n";

// ----- 8. COMPLETE FORM PROCESSING SIMULATION -----

echo "COMPLETE FORM PROCESSING\n";

function processRegistration(array $input): array
{
    $validator = new Validator();
    $sanitized = Sanitizer::trim($input);
    $sanitized = Sanitizer::stripTags($sanitized, ['name', 'bio']);

    $rules = [
        'name' => 'required|alpha_space|min:3|max:50',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
        'age' => 'required|numeric|min_value:13|max_value:120',
        'terms' => 'required|in:accepted',
    ];

    $errors = $validator->validate($sanitized, $rules);

    if ($validator->fails()) {
        OldInput::repopulate($sanitized);
        return [
            'success' => false,
            'errors' => $validator->allErrors(),
        ];
    }

    $data = $sanitized;
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    unset($data['password_confirmation'], $data['terms']);

    return [
        'success' => true,
        'data' => $data,
    ];
}

$testInput = [
    'name' => 'Budi Santoso',
    'email' => 'budi@test.com',
    'password' => 'rahasia123',
    'password_confirmation' => 'rahasia123',
    'age' => '20',
    'terms' => 'accepted',
];

$result = processRegistration($testInput);

if ($result['success']) {
    echo "Registration OK:\n";
    foreach ($result['data'] as $key => $val) {
        $display = $key === 'password' ? substr($val, 0, 20) . '...' : $val;
        echo "  $key: $display\n";
    }
} else {
    echo "Registration failed:\n";
    foreach ($result['errors'] as $err) {
        echo "  $err\n";
    }
}

// Test with validation errors
$badInput = [
    'name' => 'A',
    'email' => 'not-email',
    'password' => 'short',
    'password_confirmation' => 'different',
    'age' => '10',
    'terms' => '',
];

$badResult = processRegistration($badInput);

if (!$badResult['success']) {
    echo "\nValidation errors (expected):\n";
    foreach ($badResult['errors'] as $err) {
        echo "  $err\n";
    }
}

echo "\n";

// ----- 9. FILE UPLOAD SIMULATION -----

echo "FILE UPLOAD SIMULATION\n";

echo "File upload simulation (dijalankan via web server):\n";
echo "  - Buat form HTML dengan enctype='multipart/form-data'\n";
echo "  - \$_FILES berisi data file yang diupload\n";
echo "  - move_uploaded_file() memindahkan file\n";
echo "  - Validasi: tipe file, ukuran, dimensi gambar\n";

// Simulasi langsung
$tempFile = tempnam(sys_get_temp_dir(), 'upload_');
file_put_contents($tempFile, 'dummy image content');

$_FILES['avatar'] = [
    'name' => 'profile.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $tempFile,
    'error' => UPLOAD_ERR_OK,
    'size' => 1024,
];

$handler = new FileUploadHandler();
$handler->allowedMimes(['jpg', 'jpeg', 'png']);
$handler->maxSize(2097152);

$file = $handler->process('avatar');

if ($file) {
    echo "File valid: {$file->getClientFilename()} ({$file->getSize()} bytes)\n";
    echo "Extension: .{$file->getExtension()}\n";

    $saved = $file->store(sys_get_temp_dir() . '/uploads');
    echo "Saved to: $saved\n";
    if (file_exists($saved)) {
        unlink($saved);
    }
}

unlink($tempFile);

echo "\nSelesai belajar form handling!\n";
