CREATE TABLE IF NOT EXISTS urls (
    id bigint GENERATED BY DEFAULT AS IDENTITY UNIQUE,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS url_checks (
    id bigint GENERATED BY DEFAULT AS IDENTITY,
    url_id bigint REFERENCES urls(id) NOT NULL,
    status_code bigint NOT NULL,
    h1 TEXT,
    title VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP NOT NULL
);