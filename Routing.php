<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/GalleryController.php';
require_once 'src/controllers/OrderController.php';
require_once 'src/controllers/AdminOrderController.php';
require_once 'src/controllers/AdminUserController.php';
require_once 'src/controllers/AdminProductController.php';

class Routing
{
    // Exact path → [controller, action]
    private static array $exactRoutes = [
        ''                    => ['SecurityController',    'login'],
        'login'               => ['SecurityController',    'login'],
        'register'            => ['SecurityController',    'register'],
        'logout'              => ['SecurityController',    'logout'],
        'gallery'             => ['GalleryController',     'index'],
        'gallery/favourite'   => ['GalleryController',     'toggleFavourite'],
        'order'               => ['OrderController',       'wizard'],
        'admin/orders'        => ['AdminOrderController',  'index'],
        'admin/users'         => ['AdminUserController',   'index'],
        'admin/users/create'  => ['AdminUserController',   'create'],
        'admin/products'      => ['AdminProductController', 'index'],
        'admin/products/create' => ['AdminProductController', 'create'],
    ];

    // Regex path → [controller, action]  — first capture group becomes $id
    private static array $paramRoutes = [
        '#^admin/orders/(\d+)$#'        => ['AdminOrderController', 'show'],
        '#^admin/orders/(\d+)/status$#' => ['AdminOrderController', 'updateStatus'],
        '#^admin/orders/(\d+)/note$#'   => ['AdminOrderController', 'addNote'],
        '#^admin/users/(\d+)/update$#'  => ['AdminUserController',  'update'],
        '#^admin/users/(\d+)/delete$#'    => ['AdminUserController',  'delete'],
        '#^admin/products/(\d+)/update$#' => ['AdminProductController', 'update'],
        '#^admin/products/(\d+)/delete$#' => ['AdminProductController', 'delete'],
    ];

    public static function run(string $path): void
    {
        // Exact match
        if (array_key_exists($path, self::$exactRoutes)) {
            [$controllerClass, $action] = self::$exactRoutes[$path];
            (new $controllerClass())->$action(null);
            return;
        }

        // Parameterised match
        foreach (self::$paramRoutes as $pattern => [$controllerClass, $action]) {
            if (preg_match($pattern, $path, $matches)) {
                (new $controllerClass())->$action((int) $matches[1]);
                return;
            }
        }

        // 404
        http_response_code(404);
        include 'public/views/404.html';
    }
}
