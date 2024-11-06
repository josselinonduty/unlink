ALTER TABLE users
ADD COLUMN reset_token TEXT;

ALTER TABLE users
ADD COLUMN reset_token_expires DATETIME;