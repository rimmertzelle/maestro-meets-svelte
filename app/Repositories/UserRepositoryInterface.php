<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;

interface UserRepositoryInterface
{
    public function find(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByInviteToken(string $token): ?User;

    /** @return User[] */
    public function all(): array;

    /** @return User[] */
    public function allOwners(): array;

    /** @return Role[] */
    public function allRoles(): array;

    /** Returns the generated invite token */
    public function create(string $name, string $email, int $roleId): string;

    public function setPassword(int $id, string $passwordHash): void;

    public function clearInviteToken(int $id): void;

    /** Regenerates and returns a new invite token */
    public function generateInviteToken(int $id): string;

    public function update(int $id, string $name, string $email, int $roleId): void;

    public function updateCredentials(int $id, string $name, string $email, ?string $newPasswordHash): void;

    /** @return int[] */
    public function getCourseIds(int $userId): array;

    /** @param int[] $courseIds */
    public function setCourses(int $userId, array $courseIds): void;
}
