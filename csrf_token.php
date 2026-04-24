<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/csrf.php';

session_start();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'token_name' => csrfTokenName(),
    'token' => csrfToken(),
], JSON_UNESCAPED_SLASHES);
