<?php

namespace App\Models;

class User
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $passwordHash;
    public int $roleId;
    public string $roleName;
    public ?string $inviteToken;
    public ?string $inviteExpiresAt;
}
