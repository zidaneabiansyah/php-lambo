<?php

// ============================================
// 20 - ENCRYPTION & SECURITY
// ============================================
// Topik: Sodium encryption, OpenSSL,
//        Password hashing, Digital signatures,
//        Data integrity, Key management
// ============================================

echo "==========================================\n";
echo "  ENCRYPTION & SECURITY\n";
echo "==========================================\n\n";

// ============================================
// BAGIAN A: SODIUM ENCRYPTION
// ============================================
// libsodium adalah library enkripsi modern
// yang sudah built-in di PHP 7.2+

echo "--- 1. SODIUM: SYMMETRIC ENCRYPTION (secretbox) ---\n\n";

// Enkripsi simetris: satu kunci untuk encrypt & decrypt
// Menggunakan XSalsa20-Poly1305

$message = "Ini adalah pesan rahasia yang harus dienkripsi!";

// Generate random key (32 bytes untuk secretbox)
$key = sodium_crypto_secretbox_keygen();
echo "  Key (hex): " . bin2hex(substr($key, 0, 16)) . "...\n";
echo "  Key length: " . strlen($key) * 8 . " bits\n\n";

// Generate random nonce (24 bytes untuk secretbox)
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
echo "  Nonce (hex): " . bin2hex($nonce) . "\n\n";

// Encrypt
$ciphertext = sodium_crypto_secretbox($message, $nonce, $key);
echo "  Plaintext: $message\n";
echo "  Ciphertext (hex): " . substr(bin2hex($ciphertext), 0, 64) . "...\n";
echo "  Ciphertext length: " . strlen($ciphertext) . " bytes\n\n";

// Decrypt
$decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
if ($decrypted !== false) {
    echo "  Decrypted: $decrypted\n";
} else {
    echo "  Decrypt FAILED!\n";
}

// Test decrypt dengan key salah
$wrongKey = sodium_crypto_secretbox_keygen();
$wrongDecrypt = sodium_crypto_secretbox_open($ciphertext, $nonce, $wrongKey);
echo "  Decrypt dengan key salah: " . ($wrongDecrypt === false ? "FAILED (expected)" : "SUCCESS (unexpected)") . "\n\n";


echo "--- 2. SODIUM: GENERIC HASHING ---\n\n";

// Hashing menggunakan BLAKE2b
$data = "Data yang perlu di-hash";

// Hash dengan kunci (keyed hashing)
$hashKey = sodium_crypto_generichash_keygen();
$hash = sodium_crypto_generichash($data, $hashKey);

echo "  Data: $data\n";
echo "  BLAKE2b hash: " . bin2hex($hash) . "\n";
echo "  Hash length: " . strlen($hash) * 8 . " bits\n\n";

// Hash tanpa kunci (unkeyed)
$unkeyedHash = sodium_crypto_generichash($data);
echo "  Unkeyed hash: " . bin2hex($unkeyedHash) . "\n\n";

// Hash konsisten (deterministic)
$hash1 = sodium_crypto_generichash($data, $hashKey);
$hash2 = sodium_crypto_generichash($data, $hashKey);
echo "  Hash deterministik: " . ($hash1 === $hash2 ? "Ya (expected)" : "Tidak") . "\n\n";


echo "--- 3. SODIUM: PASSWORD HASHING (pwhash) ---\n\n";

// Password hashing yang aman menggunakan Argon2
$password = 'SuperSecretPassword123!';

// Hash password
$hash = sodium_crypto_pwhash_str(
    $password,
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
);

echo "  Password: $password\n";
echo "  Hash: $hash\n";
echo "  Hash length: " . strlen($hash) . " bytes\n\n";

// Verify password
$valid = sodium_crypto_pwhash_str_verify($hash, $password);
echo "  Verify (correct password): " . ($valid ? "VALID" : "INVALID") . "\n";

$invalid = sodium_crypto_pwhash_str_verify($hash, 'WrongPassword');
echo "  Verify (wrong password): " . ($invalid ? "VALID" : "INVALID (expected)") . "\n\n";

// Hash yang lebih kuat (sensitive data)
$strongHash = sodium_crypto_pwhash_str(
    $password,
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_SENSITIVE,
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_SENSITIVE
);
echo "  Strong hash: " . substr($strongHash, 0, 30) . "...\n\n";


echo "--- 4. SODIUM: DIGITAL SIGNATURES ---\n\n";

// Key pair untuk signing (Ed25519)
$keyPair = sodium_crypto_sign_keypair();
$signPublicKey = sodium_crypto_sign_publickey($keyPair);
$signSecretKey = sodium_crypto_sign_secretkey($keyPair);

echo "  Public key: " . bin2hex(substr($signPublicKey, 0, 16)) . "...\n";
echo "  Secret key: " . bin2hex(substr($signSecretKey, 0, 16)) . "...\n\n";

// Sign message
$message = "Dokumen penting: Kontrak kerja tahun 2024";
$signature = sodium_crypto_sign($message, $signSecretKey);
echo "  Message: $message\n";
echo "  Signature: " . bin2hex(substr($signature, 0, 32)) . "...\n";
echo "  Signed message length: " . strlen($signature) . " bytes\n\n";

// Verify signature
$verified = sodium_crypto_sign_open($signature, $signPublicKey);
echo "  Verify (correct key): " . ($verified !== false ? "VERIFIED" : "FAILED") . "\n";
echo "  Original message recovered: " . ($verified === $message ? "Ya" : "Tidak") . "\n";

// Verify dengan key salah
$wrongKeyPair = sodium_crypto_sign_keypair();
$wrongPublicKey = sodium_crypto_sign_publickey($wrongKeyPair);
$wrongVerify = sodium_crypto_sign_open($signature, $wrongPublicKey);
echo "  Verify (wrong key): " . ($wrongVerify !== false ? "VERIFIED" : "FAILED (expected)") . "\n\n";


echo "--- 5. SODIUM: KEY EXCHANGE (X25519) ---\n\n";

// Key exchange untuk established shared secret
$aliceKP = sodium_crypto_kx_keypair();
$bobKP = sodium_crypto_kx_keypair();

// Alice dan Bob masing-masing generate keypair
$alicePublic = sodium_crypto_kx_publickey($aliceKP);
$bobPublic = sodium_crypto_kx_publickey($bobKP);

// Key exchange
$aliceShared = sodium_crypto_kx_client_session_keys($aliceKP, $bobPublic);
$bobShared = sodium_crypto_kx_server_session_keys($bobKP, $alicePublic);

echo "  Alice shared recv: " . bin2hex(substr($aliceShared['rx'], 0, 16)) . "...\n";
echo "  Bob shared send:   " . bin2hex(substr($bobShared['tx'], 0, 16)) . "...\n";
echo "  Shared secret match: " . ($aliceShared['rx'] === $bobShared['tx'] ? "Ya (expected)" : "Tidak") . "\n\n";


// ============================================
// BAGIAN B: OPENSSL ENCRYPTION
// ============================================

echo "--- 6. OPENSSL: SYMMETRIC ENCRYPTION ---\n\n";

// AES-256-CBC encryption
$data = "Data sensitif yang perlu dienkripsi dengan OpenSSL";

// Generate key dan IV
$key = openssl_random_pseudo_bytes(32); // 256 bits
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

echo "  Algorithm: AES-256-CBC\n";
echo "  Key (hex): " . bin2hex(substr($key, 0, 16)) . "...\n";
echo "  IV (hex): " . bin2hex($iv) . "\n\n";

// Encrypt
$ciphertext = openssl_encrypt(
    $data,
    'aes-256-cbc',
    $key,
    OPENSSL_RAW_DATA,
    $iv
);

echo "  Plaintext: $data\n";
echo "  Ciphertext (hex): " . bin2hex(substr($ciphertext, 0, 32)) . "...\n";
echo "  Ciphertext length: " . strlen($ciphertext) . " bytes\n\n";

// Decrypt
$decrypted = openssl_decrypt(
    $ciphertext,
    'aes-256-cbc',
    $key,
    OPENSSL_RAW_DATA,
    $iv
);

echo "  Decrypted: $decrypted\n";
echo "  Match: " . ($decrypted === $data ? "Ya" : "Tidak") . "\n\n";


echo "--- 7. OPENSSL: AUTHENTICATED ENCRYPTION (AEAD) ---\n\n";

// AES-256-GCM - authenticated encryption
// Memberikan confidentiality + integrity
$data = "Data yang dienkripsi dengan autentikasi";
$key = openssl_random_pseudo_bytes(32);
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-gcm'));
$aad = "additional authenticated data"; // Data yang di-authenticate tapi tidak di-encrypt

// Encrypt dengan GCM
$ciphertext = openssl_encrypt(
    $data,
    'aes-256-gcm',
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag,       // Authentication tag (output)
    $aad,       // Additional authenticated data
    16          // Tag length
);

echo "  Ciphertext (hex): " . bin2hex(substr($ciphertext, 0, 32)) . "...\n";
echo "  Auth tag (hex): " . bin2hex($tag) . "\n\n";

// Decrypt dengan GCM + verify tag
$decrypted = openssl_decrypt(
    $ciphertext,
    'aes-256-gcm',
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag,
    $aad
);

echo "  Decrypted: $decrypted\n";
echo "  Auth tag verified: " . ($decrypted !== false ? "Ya" : "Tidak") . "\n\n";


echo "--- 8. OPENSSL: DIGITAL SIGNATURES ---\n\n";

// Generate key pair RSA
$privateKey = openssl_pkey_new([
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
]);

$publicKey = openssl_pkey_get_details($privateKey)['key'];

echo "  RSA key generated (2048 bits)\n\n";

// Sign data
$data = "Dokumen kontrak: Rp 100.000.000";
openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

echo "  Data: $data\n";
echo "  Signature (hex): " . bin2hex(substr($signature, 0, 32)) . "...\n";
echo "  Signature length: " . strlen($signature) . " bytes\n\n";

// Verify signature
$verified = openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256);
echo "  Verify (correct data): " . ($verified === 1 ? "VERIFIED" : "FAILED") . "\n";

// Verify dengan data yang diubah
$tampered = "Dokumen kontrak: Rp 999.999.999";
$tamperedVerify = openssl_verify($tampered, $signature, $publicKey, OPENSSL_ALGO_SHA256);
echo "  Verify (tampered data): " . ($tamperedVerify === 1 ? "VERIFIED" : "FAILED (expected)") . "\n\n";


echo "--- 9. OPENSSL: ENCRYPT/DECRYPT FILE ---\n\n";

// Simulasi enkripsi file
function encryptFile(string $input, string $output, string $password): bool
{
    $cipher = 'aes-256-cbc';
    $key = openssl_digest($password, 'sha256', true);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

    $data = file_get_contents($input);
    $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    // Simpan IV + ciphertext
    $result = $iv . $encrypted;
    file_put_contents($output, $result);

    return true;
}

function decryptFile(string $input, string $password): string
{
    $cipher = 'aes-256-cbc';
    $key = openssl_digest($password, 'sha256', true);
    $ivLength = openssl_cipher_iv_length($cipher);

    $data = file_get_contents($input);
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);

    return openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
}

// Demo (tidak write ke file system)
$tempDir = sys_get_temp_dir();
$inputFile = $tempDir . '/php_lambo_test_input.txt';
$outputFile = $tempDir . '/php_lambo_test_encrypted.bin';

file_put_contents($inputFile, "Ini isi file yang akan dienkripsi!\nBaris kedua: data rahasia.");

encryptFile($inputFile, $outputFile, 'MySecurePassword123!');
$decryptedContent = decryptFile($outputFile, 'MySecurePassword123!');

echo "  Original: " . file_get_contents($inputFile) . "\n";
echo "  Decrypted: $decryptedContent\n";
echo "  Match: " . ($decryptedContent === file_get_contents($inputFile) ? "Ya" : "Tidak") . "\n\n";

// Cleanup
unlink($inputFile);
unlink($outputFile);


// ============================================
// BAGIAN C: PASSWORD SECURITY
// ============================================

echo "--- 10. PASSWORD HASHING & VERIFICATION ---\n\n";

// password_hash() dan password_verify() menggunakan bcrypt

$passwords = [
    'UserPassword123!',
    'AnotherP@ssw0rd',
    'SimplePass',
];

foreach ($passwords as $pw) {
    // Hash password
    $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
    echo "  Password: $pw\n";
    echo "  Hash: $hash\n";

    // Verify
    $valid = password_verify($pw, $hash);
    echo "  Verify: " . ($valid ? "VALID" : "INVALID") . "\n";

    // Check if rehash needed
    $needsRehash = password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    echo "  Needs rehash: " . ($needsRehash ? "Ya" : "Tidak") . "\n\n";
}

// Argon2id (PHP 7.3+)
echo "  --- Argon2id ---\n";
$argonHash = password_hash('SecurePass123!', PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,  // 64 MB
    'time_cost' => 4,        // 4 seconds
    'threads' => 3,          // 3 threads
]);
echo "  Argon2id hash: " . substr($argonHash, 0, 40) . "...\n";
echo "  Verify: " . (password_verify('SecurePass123!', $argonHash) ? "VALID" : "INVALID") . "\n\n";


echo "--- 11. ENCRYPTION UTILITY CLASS ---\n\n";

class EncryptionService
{
    private string $key;

    public function __construct(string $secret)
    {
        // Derive key dari secret
        $this->key = sodium_crypto_generichash($secret, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->key);
        return base64_encode($nonce . $ciphertext);
    }

    public function decrypt(string $encoded): string|false
    {
        $decoded = base64_decode($encoded);
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);
    }

    public function hashData(string $data): string
    {
        return bin2hex(sodium_crypto_generichash($data, $this->key));
    }

    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

// Demo
$encService = new EncryptionService('my-secret-key');

$secretData = "Nomor Kartu Kredit: 4111-1111-1111-1111";
$encrypted = $encService->encrypt($secretData);
$decrypted = $encService->decrypt($encrypted);

echo "  Secret: $secretData\n";
echo "  Encrypted: $encrypted\n";
echo "  Decrypted: $decrypted\n";
echo "  Match: " . ($decrypted === $secretData ? "Ya" : "Tidak") . "\n\n";

// Hash
$hash = $encService->hashData("data integrity check");
echo "  Data hash: $hash\n";

// Token
$token = $encService->generateToken();
echo "  Random token: $token\n\n";


echo "==========================================\n";
echo "  RINGKASAN\n";
echo "==========================================\n";
echo "\n";
echo "SODIUM (Recommended untuk PHP 7.2+):\n";
echo "  - secretbox: Symmetric encryption (XSalsa20-Poly1305)\n";
echo "  - generichash: BLAKE2b hashing\n";
echo "  - pwhash: Argon2 password hashing\n";
echo "  - sign: Ed25519 digital signatures\n";
echo "  - kx: X25519 key exchange\n";
echo "\n";
echo "OPENSSL:\n";
echo "  - aes-256-cbc/gcm: Symmetric encryption\n";
echo "  - RSA: Digital signatures & asymmetric encryption\n";
echo "  - File encryption/decryption\n";
echo "\n";
echo "PASSWORD:\n";
echo "  - password_hash(): Bcrypt/Argon2 hashing\n";
echo "  - password_verify(): Verify password\n";
echo "  - password_needs_rehash(): Check hash age\n";
echo "\n";

echo "Selesai!\n";
