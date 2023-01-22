DROP TABLE IF EXISTS urls;

CREATE TABLE urls (
    id         integer PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name       varchar(255) NOT NULL,
    created_at TIMESTAMP(0) NOT NULL
);