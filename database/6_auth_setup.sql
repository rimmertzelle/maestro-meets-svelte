-- =============================================================
-- Auth seed: roles
-- The default admin user is created via PHP in the maestro
-- migrate command so the password can be properly hashed.
-- =============================================================

INSERT INTO role (name, description) VALUES
    ('admin', 'Full access to all resources'),
    ('owner', 'Can edit their own assigned courses');
