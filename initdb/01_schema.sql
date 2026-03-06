CREATE TABLE IF NOT EXISTS searches (
    id SERIAL PRIMARY KEY,
    query TEXT NOT NULL,
    ip VARCHAR(64) NOT NULL,
    country VARCHAR(64),
    city VARCHAR(64),
    lat NUMERIC(9,5),
    lon NUMERIC(9,5),
    user_agent TEXT,
    searched_at TIMESTAMP DEFAULT NOW()
);
