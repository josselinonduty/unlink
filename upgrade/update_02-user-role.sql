ALTER TABLE users
ADD COLUMN role TEXT DEFAULT 'user';

-- Set the first user as admin (if exists)
UPDATE users
SET
    role = 'admin'
WHERE
    rowid = (
        SELECT
            rowid
        FROM
            users
        ORDER BY
            rowid ASC
        LIMIT
            1
    );