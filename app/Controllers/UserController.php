<?php

namespace App\Controllers;

use App\Auth;
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

    public function index(Request $request): Response
    {
        if ($guard = $this->auth->guardAdmin()) {
            return $guard;
        }

        $invite = $request->get('invite');

        return $this->responseFactory->view('users/index.html.twig', [
            'users'  => $this->userRepository->all(),
            'roles'  => $this->userRepository->allRoles(),
            'invite' => $invite !== null && $invite !== '' ? $invite : null,
        ]);
    }

    public function create(Request $request): Response
    {
        if ($guard = $this->auth->guardAdmin()) {
            return $guard;
        }

        $name = $request->get('name') ?? '';
        $email = $request->get('email') ?? '';
        $roleId = (int)($request->get('role_id') ?? 2);

        if ($name === '' || $email === '') {
            return $this->responseFactory->view('users/index.html.twig', [
                'users'  => $this->userRepository->all(),
                'roles'  => $this->userRepository->allRoles(),
                'error'  => 'Name and email are required.',
            ]);
        }

        $token = $this->userRepository->create($name, $email, $roleId);

        return $this->responseFactory->redirect('/admin/users?invite=' . urlencode($token));
    }

    public function update(Request $request): Response
    {
        if ($guard = $this->auth->guardAdmin()) {
            return $guard;
        }

        $id = (int)$request->get('id');
        $name = $request->get('name') ?? '';
        $email = $request->get('email') ?? '';
        $roleId = (int)($request->get('role_id') ?? 2);

        $this->userRepository->update($id, $name, $email, $roleId);

        return $this->responseFactory->redirect('/admin/users');
    }

    public function resendInvite(Request $request): Response
    {
        if ($guard = $this->auth->guardAdmin()) {
            return $guard;
        }

        $id = (int)$request->get('id');
        $token = $this->userRepository->generateInviteToken($id);

        return $this->responseFactory->redirect('/admin/users?invite=' . urlencode($token));
    }
}
