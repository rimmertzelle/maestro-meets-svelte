<?php

namespace App\Controllers\Api;

use App\Auth;
use App\Models\User;
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

    public function me(): Response
    {
        if ($guard = $this->auth->guardApi()) {
            return $guard;
        }
        $user = $this->auth->currentUser();
        if ($user === null) {
            return $this->responseFactory->json(['error' => 'Unauthenticated.'], 401);
        }
        return $this->responseFactory->json(['user' => $this->serializeUser($user)]);
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
            return $this->responseFactory->json(['error' => 'Invalid email or password.'], 422);
        }

        $this->auth->login($user->id);
        return $this->responseFactory->json(['user' => $this->serializeUser($user)]);
    }

    public function logout(): Response
    {
        $this->auth->logout();
        return $this->responseFactory->json(['success' => true]);
    }

    public function showInvite(Request $request): Response
    {
        $token = $request->get('token') ?? '';
        $user = $this->userRepository->findByInviteToken($token);

        if ($user === null) {
            return $this->responseFactory->json(['error' => 'This invite link is invalid or has expired.'], 422);
        }

        return $this->responseFactory->json(['user' => ['name' => $user->name, 'email' => $user->email]]);
    }

    public function acceptInvite(Request $request): Response
    {
        $token = $request->get('token') ?? '';
        $password = $request->get('password') ?? '';
        $passwordConfirm = $request->get('password_confirm') ?? '';

        $user = $this->userRepository->findByInviteToken($token);

        if ($user === null) {
            return $this->responseFactory->json(['error' => 'This invite link is invalid or has expired.'], 422);
        }

        if (strlen($password) < 8) {
            return $this->responseFactory->json(['error' => 'Password must be at least 8 characters.'], 422);
        }

        if ($password !== $passwordConfirm) {
            return $this->responseFactory->json(['error' => 'Passwords do not match.'], 422);
        }

        $this->userRepository->setPassword($user->id, password_hash($password, PASSWORD_BCRYPT));
        $this->userRepository->clearInviteToken($user->id);
        $this->auth->login($user->id);

        return $this->responseFactory->json(['user' => $this->serializeUser($user)]);
    }

    public function profile(): Response
    {
        if ($guard = $this->auth->guardApi()) {
            return $guard;
        }
        $user = $this->auth->currentUser();
        if ($user === null) {
            return $this->responseFactory->json(['error' => 'Unauthenticated.'], 401);
        }
        return $this->responseFactory->json(['user' => $this->serializeUser($user)]);
    }

    public function updateProfile(Request $request): Response
    {
        if ($guard = $this->auth->guardApi()) {
            return $guard;
        }

        $currentUser = $this->auth->currentUser();
        if ($currentUser === null) {
            return $this->responseFactory->json(['error' => 'Unauthenticated.'], 401);
        }

        $name = $request->get('name') ?? $currentUser->name;
        $email = $request->get('email') ?? $currentUser->email;
        $password = $request->get('password') ?? '';
        $passwordConfirm = $request->get('password_confirm') ?? '';

        if ($password !== '') {
            if (strlen($password) < 8) {
                return $this->responseFactory->json(['error' => 'New password must be at least 8 characters.'], 422);
            }
            if ($password !== $passwordConfirm) {
                return $this->responseFactory->json(['error' => 'Passwords do not match.'], 422);
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

        $updated = $this->userRepository->find($currentUser->id);
        if ($updated === null) {
            return $this->responseFactory->json(['error' => 'User not found.'], 404);
        }
        return $this->responseFactory->json(['user' => $this->serializeUser($updated)]);
    }

    /** @return array<string, mixed> */
    private function serializeUser(User $user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->roleName,
        ];
    }
}
