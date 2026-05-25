<?php
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
	|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

session_set_cookie_params([
	'lifetime' => 0,
	'path' => '/',
	'secure' => $isSecure,
	'httponly' => true,
	'samesite' => 'Lax',
]);
session_start();

include 'Routing.php';

// Sanitize the path to prevent XSS attacks
$path = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
// Extract the path without query parameters
$path = parse_url($path, PHP_URL_PATH);

Routing::run(ltrim($path, '/'));
