<?php
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '5432';
$dbName = getenv('DB_NAME') ?: 'pdao_db';
$dbUser = getenv('DB_USER') ?: getenv('USER') ?: 'postgres';
$dbPassword = getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: '';

$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl !== false && !empty($databaseUrl)) {
    $components = parse_url($databaseUrl);
    if ($components !== false) {
        $dbHost = $components['host'] ?? $dbHost;
        $dbPort = $components['port'] ?? $dbPort;
        $dbName = ltrim($components['path'] ?? $dbName, '/');
        $dbUser = $components['user'] ?? $dbUser;
        $dbPassword = $components['pass'] ?? $dbPassword;
    }
}

$connectionString = sprintf(
    'host=%s port=%s dbname=%s user=%s',
    $dbHost,
    $dbPort,
    $dbName,
    $dbUser
);

if ($dbPassword !== '') {
    $connectionString .= ' password=' . $dbPassword;
}

$conn = pg_connect($connectionString);
if (!$conn) {
    error_log('Database connection failed: ' . pg_last_error());
    http_response_code(500);
    exit('Database unavailable. Please try again later.');
}
