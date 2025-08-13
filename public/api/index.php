<?php
declare(strict_types=1);

require __DIR__.'/bootstrap/autoload.php';

use Core\Router;
use Middleware\CorsMiddleware;
use Middleware\AuthMiddleware;
use Middleware\RateLimitMiddleware;

// Build router + global middleware
$router = new Router();
$router->use(new CorsMiddleware());
$router->use(new RateLimitMiddleware('global'));
$router->use(new AuthMiddleware()); // no-op if API_TOKEN is empty

// Register routes
require __DIR__.'/bootstrap/routes.php';

// Dispatch
$router->dispatch();
