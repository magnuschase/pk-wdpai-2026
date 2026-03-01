<?php

include 'Routing.php';

// Sanitize the path to prevent XSS attacks
$path = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
// Extract the path without query parameters
$path = parse_url($path, PHP_URL_PATH);

Routing::run(ltrim($path, '/'));