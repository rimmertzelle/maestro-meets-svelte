-- =============================================================
-- Starter — Authentication schema
-- =============================================================

CREATE TABLE role (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE `user` (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    name              TEXT NOT NULL,
    email             TEXT NOT NULL UNIQUE,
    password_hash     TEXT NULL,
    invite_token      TEXT NULL UNIQUE,
    invite_expires_at TEXT NULL,
    role_id           INTEGER NOT NULL,
    created_at        TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES role (id)
);
