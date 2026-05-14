<?php

namespace App;

use App\Controllers\Api\AuthController as ApiAuthController;
use App\Controllers\Api\UserController as ApiUserController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use Exception;
use Framework\Database;
use Framework\ResponseFactory;
use Framework\ServiceContainer;
use Framework\ServiceProviderInterface;
use Framework\Session;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws Exception
     */
    public function register(ServiceContainer $container): void
    {
        $responseFactory = $container->get(ResponseFactory::class);
        $database = $container->get(Database::class);

        $session = new Session();
        $session->start();
        $container->set(Session::class, $session);

        $userRepository = new UserRepository($database);
        $container->set(UserRepositoryInterface::class, $userRepository);

        $auth = new Auth($session, $userRepository, $responseFactory);
        $container->set(Auth::class, $auth);
        $responseFactory->addGlobal('auth', $auth);

        $homeController = new HomeController($responseFactory);
        $container->set(HomeController::class, $homeController);

        $authController = new AuthController($responseFactory, $auth, $userRepository);
        $container->set(AuthController::class, $authController);

        $userController = new UserController($responseFactory, $auth, $userRepository);
        $container->set(UserController::class, $userController);

        $apiAuthController = new ApiAuthController($responseFactory, $auth, $userRepository);
        $container->set(ApiAuthController::class, $apiAuthController);

        $apiUserController = new ApiUserController($responseFactory, $auth, $userRepository);
        $container->set(ApiUserController::class, $apiUserController);
    }
}
