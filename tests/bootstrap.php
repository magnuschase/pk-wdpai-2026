<?php

// EnvLoader::load() skips missing files, so tests run fine even without a real .env.
require_once __DIR__ . '/../EnvLoader.php';
EnvLoader::load(__DIR__ . '/../.env');

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../src/models/GalleryItem.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/repositories/Repository.php';
require_once __DIR__ . '/../src/repositories/GalleryItemsRepository.php';
require_once __DIR__ . '/../src/repositories/UsersRepository.php';

/**
 * Inject a mock PDO into the Database singleton.
 * Repositories call Database::getInstance()->connect(), which returns $conn immediately when set.
 */
function injectMockPdo(PDO $pdo): void
{
    $db = Database::getInstance();
    $prop = (new ReflectionClass(Database::class))->getProperty('conn');
    $prop->setAccessible(true);
    $prop->setValue($db, $pdo);
}
