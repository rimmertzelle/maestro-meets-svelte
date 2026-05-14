<?php

namespace App;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;

class Auth
{
    private Session $session;

    private UserRepositoryInterface $userRepository;

    private ResponseFactory $responseFactory;

    private ?User $cachedUser = null;

    private bool $userLoaded = false;

    public function __construct(
        Session $session,
        UserRepositoryInterface $userRepository,
        ResponseFactory $responseFactory
    ) {
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->responseFactory = $responseFactory;
    }

    public function currentUser(): ?User
    {
        if (!$this->userLoaded) {
            $userId = $this->session->get('user_id');
            $this->cachedUser = is_int($userId) ? $this->userRepository->find($userId) : null;
            $this->userLoaded = true;
        }
        return $this->cachedUser;
    }

    public function currentUserId(): ?int
    {
        return $this->currentUser()?->id;
    }

    public function isLoggedIn(): bool
    {
        return $this->currentUser() !== null;
    }

    public function isAdmin(): bool
    {
        return $this->currentUser()?->roleName === 'admin';
    }

    public function canEditCourse(?int $courseOwnerId): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        if ($this->isAdmin()) {
            return true;
        }
        return $courseOwnerId !== null && $courseOwnerId === $this->currentUserId();
    }

    public function login(int $userId): void
    {
        $this->session->set('user_id', $userId);
        $this->cachedUser = null;
        $this->userLoaded = false;
    }

    public function logout(): void
    {
        $this->session->remove('user_id');
        $this->cachedUser = null;
        $this->userLoaded = false;
    }

    /** Returns a redirect Response if not logged in, null if OK to proceed. */
    public function guard(): ?Response
    {
        if (!$this->isLoggedIn()) {
            return $this->responseFactory->redirect('/login');
        }
        return null;
    }

    /** Returns a 403 Response if not admin, null if OK to proceed. */
    public function guardAdmin(): ?Response
    {
        if ($guard = $this->guard()) {
            return $guard;
        }
        if (!$this->isAdmin()) {
            return $this->responseFactory->unauthorized();
        }
        return null;
    }
}
