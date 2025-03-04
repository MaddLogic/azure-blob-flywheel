<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Explicitly specify the correct environment file (.env.testing)
$dotenv = Dotenv::createImmutable(__DIR__ . '/..', '.env.testing');

try {
    $dotenv->load();
    echo "✅ .env.testing loaded successfully!\n";
    echo "🔹 AZURE_STORAGE_ACCOUNT: " . $_ENV['AZURE_STORAGE_ACCOUNT'] . "\n";
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "⚠️  Error: .env.testing file is missing or unreadable!\n";
    exit(1);

}
