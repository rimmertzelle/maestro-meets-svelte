<?php

namespace App\Controllers\Api;

use App\Auth;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class UserController
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

    public function index(): Response
    {
        if ($guard = $this->auth->guardAdminApi()) {
            return $guard;
        }

        return $this->responseFactory->json([
            'users' => array_map(fn(User $u): array => $this->serializeUser($u), $this->userRepository->all()),
            'roles' => array_map(fn(Role $r): array => $this->serializeRole($r), $this->userRepository->allRoles()),
        ]);
    }

    public function create(Request $request): Response
    {
        if ($guard = $this->auth->guardAdminApi()) {
            return $guard;
        }

        $name   = $request->get('name') ?? '';
        $email  = $request->get('email') ?? '';
        $roleId = (int) ($request->get('role_id') ?? 2);

        if ($name === '' || $email === '') {
            return $this->responseFactory->json(['error' => 'Name and email are required.'], 422);
        }

        $token = $this->userRepository->create($name, $email, $roleId);

        return $this->responseFactory->json(['token' => $token], 201);
    }

    public function update(Request $request): Response
    {
        if ($guard = $this->auth->guardAdminApi()) {
            return $guard;
        }

        $id     = (int) $request->get('id');
        $name   = $request->get('name') ?? '';
        $email  = $request->get('email') ?? '';
        $roleId = (int) ($request->get('role_id') ?? 2);

        $this->userRepository->update($id, $name, $email, $roleId);

        $user = $this->userRepository->find($id);
        if ($user === null) {
            return $this->responseFactory->json(['error' => 'User not found.'], 404);
        }

        return $this->responseFactory->json(['user' => $this->serializeUser($user)]);
    }

    public function resendInvite(Request $request): Response
    {
        if ($guard = $this->auth->guardAdminApi()) {
            return $guard;
        }

        $id    = (int) $request->get('id');
        $token = $this->userRepository->generateInviteToken($id);

        return $this->responseFactory->json(['token' => $token]);
    }

    /** @return array<string, mixed> */
    private function serializeUser(User $user): array
    {
        return [
            'id'               => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'role_id'          => $user->roleId,
            'role'             => $user->roleName,
            'has_password'     => $user->passwordHash !== null,
            'invite_token'     => $user->inviteToken,
            'invite_expires_at' => $user->inviteExpiresAt,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeRole(Role $role): array
    {
        return [
            'id'          => $role->id,
            'name'        => $role->name,
            'description' => $role->description,
        ];
    }
}
