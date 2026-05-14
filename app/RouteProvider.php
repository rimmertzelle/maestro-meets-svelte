<?php

namespace App;

use App\Controllers\Api\AuthController as ApiAuthController;
use App\Controllers\Api\UserController as ApiUserController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use Framework\RouteProviderInterface;
use Framework\Router;
use Framework\ServiceContainer;

class RouteProvider implements RouteProviderInterface
{
    /**
     * @throws \Exception
     */
    public function register(Router $router, ServiceContainer $container): void
    {
        $homeController = $container->get(HomeController::class);
        $router->addRoute('GET', '/', [$homeController, 'index']);

        $authController = $container->get(AuthController::class);
        $router->addRoute('GET', '/login', [$authController, 'showLogin']);
        $router->addRoute('POST', '/login', [$authController, 'login']);
        $router->addRoute('POST', '/logout', [$authController, 'logout']);
        $router->addRoute('GET', '/invite/(?<token>[a-f0-9]+)', [$authController, 'showSetPassword']);
        $router->addRoute('POST', '/invite/(?<token>[a-f0-9]+)', [$authController, 'setPassword']);
        $router->addRoute('GET', '/profile', [$authController, 'showProfile']);
        $router->addRoute('POST', '/profile', [$authController, 'updateProfile']);

        $userController = $container->get(UserController::class);
        $router->addRoute('GET', '/admin/users', [$userController, 'index']);
        $router->addRoute('POST', '/admin/users', [$userController, 'create']);
        $router->addRoute('POST', '/admin/users/(?<id>\d+)', [$userController, 'update']);
        $router->addRoute('POST', '/admin/users/(?<id>\d+)/invite', [$userController, 'resendInvite']);

        $apiAuth = $container->get(ApiAuthController::class);
        $router->addRoute('GET', '/api/auth/me', [$apiAuth, 'me']);
        $router->addRoute('POST', '/api/auth/login', [$apiAuth, 'login']);
        $router->addRoute('POST', '/api/auth/logout', [$apiAuth, 'logout']);
        $router->addRoute('GET', '/api/auth/invite/(?<token>[a-f0-9]+)', [$apiAuth, 'showInvite']);
        $router->addRoute('POST', '/api/auth/invite/(?<token>[a-f0-9]+)', [$apiAuth, 'acceptInvite']);
        $router->addRoute('GET', '/api/auth/profile', [$apiAuth, 'profile']);
        $router->addRoute('POST', '/api/auth/profile', [$apiAuth, 'updateProfile']);

        $apiUsers = $container->get(ApiUserController::class);
        $router->addRoute('GET', '/api/users', [$apiUsers, 'index']);
        $router->addRoute('POST', '/api/users', [$apiUsers, 'create']);
        $router->addRoute('POST', '/api/users/(?<id>\d+)', [$apiUsers, 'update']);
        $router->addRoute('POST', '/api/users/(?<id>\d+)/invite', [$apiUsers, 'resendInvite']);
    }
}
