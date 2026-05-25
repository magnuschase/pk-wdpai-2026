<?php

// Include config first so Database.php's bare require_once "config.php" is skipped via require_once dedup.
require_once __DIR__ . '/../config.php';
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
