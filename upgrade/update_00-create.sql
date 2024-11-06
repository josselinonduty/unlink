CREATE TABLE
    IF NOT EXISTS users (
        email TEXT NOT NULL UNIQUE,
        token TEXT,
        password_hash TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    IF NOT EXISTS links (
        shortid TEXT PRIMARY KEY,
        source_url TEXT NOT NULL,
        owner_email TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        deleting_at DATETIME DEFAULT NULL,
        views INTEGER DEFAULT 0,
        FOREIGN KEY (owner_email) REFERENCES users (email)
    );

CREATE INDEX IF NOT EXISTS idx_links_owner ON links (owner_email);

CREATE INDEX IF NOT EXISTS idx_links_delete_date ON links (deleting_at);