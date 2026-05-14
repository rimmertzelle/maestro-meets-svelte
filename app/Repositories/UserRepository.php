<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Framework\Database;

class UserRepository implements UserRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function find(int $id): ?User
    {
        $row = $this->database->run(
            "SELECT u.*, r.name AS role_name
             FROM `user` u
             JOIN role r ON r.id = u.role_id
             WHERE u.id = :id",
            ['id' => $id]
        )->fetch();

        if (!$row) {
            return null;
        }

        return $this->fromDbRow($row);
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->database->run(
            "SELECT u.*, r.name AS role_name
             FROM `user` u
             JOIN role r ON r.id = u.role_id
             WHERE u.email = :email",
            ['email' => $email]
        )->fetch();

        if (!$row) {
            return null;
        }

        return $this->fromDbRow($row);
    }

    public function findByInviteToken(string $token): ?User
    {
        $row = $this->database->run(
            "SELECT u.*, r.name AS role_name
             FROM `user` u
             JOIN role r ON r.id = u.role_id
             WHERE u.invite_token = :token
               AND u.invite_expires_at > NOW()",
            ['token' => $token]
        )->fetch();

        if (!$row) {
            return null;
        }

        return $this->fromDbRow($row);
    }

    /**
     * @return User[]
     */
    public function all(): array
    {
        $rows = $this->database->run(
            "SELECT u.*, r.name AS role_name
             FROM `user` u
             JOIN role r ON r.id = u.role_id
             ORDER BY u.name"
        )->fetchAll();

        return array_map([$this, 'fromDbRow'], $rows);
    }

    /**
     * @return User[]
     */
    public function allOwners(): array
    {
        $rows = $this->database->run(
            "SELECT u.*, r.name AS role_name
             FROM `user` u
             JOIN role r ON r.id = u.role_id
             WHERE r.name = 'owner'
             ORDER BY u.name"
        )->fetchAll();

        return array_map([$this, 'fromDbRow'], $rows);
    }

    /**
     * @return Role[]
     */
    public function allRoles(): array
    {
        $rows = $this->database->run("SELECT * FROM role ORDER BY id")->fetchAll();

        return array_map(function (mixed $row): Role {
            $role = new Role();
            $role->id = $row->id;
            $role->name = $row->name;
            $role->description = $row->description;
            return $role;
        }, $rows);
    }

    public function create(string $name, string $email, int $roleId): string
    {
        $token = bin2hex(random_bytes(32));
        $this->database->run(
            "INSERT INTO `user` (name, email, role_id, invite_token, invite_expires_at)
             VALUES (:name, :email, :role_id, :token, DATE_ADD(NOW(), INTERVAL 72 HOUR))",
            ['name' => $name, 'email' => $email, 'role_id' => $roleId, 'token' => $token]
        );
        return $token;
    }

    public function setPassword(int $id, string $passwordHash): void
    {
        $this->database->run(
            "UPDATE `user` SET password_hash = :hash WHERE id = :id",
            ['hash' => $passwordHash, 'id' => $id]
        );
    }

    public function clearInviteToken(int $id): void
    {
        $this->database->run(
            "UPDATE `user` SET invite_token = NULL, invite_expires_at = NULL WHERE id = :id",
            ['id' => $id]
        );
    }

    public function generateInviteToken(int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $this->database->run(
            "UPDATE `user`
             SET invite_token = :token, invite_expires_at = DATE_ADD(NOW(), INTERVAL 72 HOUR)
             WHERE id = :id",
            ['token' => $token, 'id' => $id]
        );
        return $token;
    }

    public function update(int $id, string $name, string $email, int $roleId): void
    {
        $this->database->run(
            "UPDATE `user` SET name = :name, email = :email, role_id = :role_id WHERE id = :id",
            ['name' => $name, 'email' => $email, 'role_id' => $roleId, 'id' => $id]
        );
    }

    /**
     * @return int[]
     */
    public function getCourseIds(int $userId): array
    {
        $rows = $this->database->run(
            "SELECT course_id FROM user_course WHERE user_id = :user_id",
            ['user_id' => $userId]
        )->fetchAll();

        return array_map(fn(mixed $r): int => (int) $r->course_id, $rows);
    }

    /**
     * @param int[] $courseIds
     */
    public function setCourses(int $userId, array $courseIds): void
    {
        $this->database->run(
            "DELETE FROM user_course WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        foreach ($courseIds as $courseId) {
            $this->database->run(
                "INSERT INTO user_course (user_id, course_id) VALUES (:user_id, :course_id)",
                ['user_id' => $userId, 'course_id' => $courseId]
            );
        }
    }

    public function updateCredentials(int $id, string $name, string $email, ?string $newPasswordHash): void
    {
        if ($newPasswordHash !== null) {
            $this->database->run(
                "UPDATE `user` SET name = :name, email = :email, password_hash = :hash WHERE id = :id",
                ['name' => $name, 'email' => $email, 'hash' => $newPasswordHash, 'id' => $id]
            );
        } else {
            $this->database->run(
                "UPDATE `user` SET name = :name, email = :email WHERE id = :id",
                ['name' => $name, 'email' => $email, 'id' => $id]
            );
        }
    }

    private function fromDbRow(mixed $row): User
    {
        $user = new User();
        $user->id = $row->id;
        $user->name = $row->name;
        $user->email = $row->email;
        $user->passwordHash = $row->password_hash;
        $user->roleId = $row->role_id;
        $user->roleName = $row->role_name;
        $user->inviteToken = $row->invite_token;
        $user->inviteExpiresAt = $row->invite_expires_at;
        return $user;
    }
}
