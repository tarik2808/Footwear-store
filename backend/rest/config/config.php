<?php
// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file_get_contents(__DIR__ . '/../.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Default configuration values
$config = [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'your-secret-key-change-this-in-production',
    'jwt_expiry' => 3600, // 1 hour
    'jwt_algorithm' => 'HS256',
    'debug' => true
];

// Export configuration values
foreach ($config as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
}

return $config;
?>
