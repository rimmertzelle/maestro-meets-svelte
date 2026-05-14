<?php

namespace App\Controllers;

use App\Auth;
use App\Repositories\UserRepositoryInterface;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class AuthController
{
    private ResponseFactory $responseFactory;

    private Auth $auth;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ResponseFactory $responseFactory,
        Auth $auth,
        UserRepositoryInterface $userRepository
    ) {
        $this->responseFactory = $responseFactory;
        $this->auth = $auth;
        $this->userRepository = $userRepository;
    }

    public function showLogin(): Response
    {
        if ($this->auth->isLoggedIn()) {
            return $this->responseFactory->redirect('/');
        }
        return $this->responseFactory->view('auth/login.html.twig');
    }

    public function login(Request $request): Response
    {
        $email = $request->get('email') ?? '';
        $password = $request->get('password') ?? '';

        $user = $this->userRepository->findByEmail($email);

        if (
            $user === null
            || $user->passwordHash === null
            || !password_verify($password, $user->passwordHash)
        ) {
            return $this->responseFactory->view('auth/login.html.twig', [
                'error' => 'Invalid email or password.',
                'email' => $email,
            ]);
        }

        $this->auth->login($user->id);
        return $this->responseFactory->redirect('/');
    }

    public function logout(): Response
    {
        $this->auth->logout();
        return $this->responseFactory->redirect('/login');
    }

    public function showSetPassword(Request $request): Response
    {
        $token = $request->get('token') ?? '';
        $user = $this->userRepository->findByInviteToken($token);

        if ($user === null) {
            return $this->responseFactory->view('auth/set_password.html.twig', [
                'error' => 'This invite link is invalid or has expired.',
                'token' => $token,
                'invalid' => true,
            ]);
        }

        return $this->responseFactory->view('auth/set_password.html.twig', [
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function setPassword(Request $request): Response
    {
        $token = $request->get('token') ?? '';
        $password = $request->get('password') ?? '';
        $passwordConfirm = $request->get('password_confirm') ?? '';

        $user = $this->userRepository->findByInviteToken($token);

        if ($user === null) {
            return $this->responseFactory->view('auth/set_password.html.twig', [
                'error' => 'This invite link is invalid or has expired.',
                'token' => $token,
                'invalid' => true,
            ]);
        }

        if (strlen($password) < 8) {
            return $this->responseFactory->view('auth/set_password.html.twig', [
                'error' => 'Password must be at least 8 characters.',
                'token' => $token,
                'user' => $user,
            ]);
        }

        if ($password !== $passwordConfirm) {
            return $this->responseFactory->view('auth/set_password.html.twig', [
                'error' => 'Passwords do not match.',
                'token' => $token,
                'user' => $user,
            ]);
        }

        $this->userRepository->setPassword($user->id, password_hash($password, PASSWORD_BCRYPT));
        $this->userRepository->clearInviteToken($user->id);
        $this->auth->login($user->id);

        return $this->responseFactory->redirect('/');
    }

    public function showProfile(): Response
    {
        if ($guard = $this->auth->guard()) {
            return $guard;
        }
        return $this->responseFactory->view('auth/profile.html.twig');
    }

    public function updateProfile(Request $request): Response
    {
        if ($guard = $this->auth->guard()) {
            return $guard;
        }

        $currentUser = $this->auth->currentUser();
        if ($currentUser === null) {
            return $this->responseFactory->redirect('/login');
        }

        $name = $request->get('name') ?? $currentUser->name;
        $email = $request->get('email') ?? $currentUser->email;
        $password = $request->get('password') ?? '';
        $passwordConfirm = $request->get('password_confirm') ?? '';

        if ($password !== '') {
            if (strlen($password) < 8) {
                return $this->responseFactory->view('auth/profile.html.twig', [
                    'error' => 'New password must be at least 8 characters.',
                ]);
            }
            if ($password !== $passwordConfirm) {
                return $this->responseFactory->view('auth/profile.html.twig', [
                    'error' => 'Passwords do not match.',
                ]);
            }
            $this->userRepository->updateCredentials(
                $currentUser->id,
                $name,
                $email,
                password_hash($password, PASSWORD_BCRYPT)
            );
        } else {
            $this->userRepository->updateCredentials($currentUser->id, $name, $email, null);
        }

        return $this->responseFactory->redirect('/profile');
    }
}
