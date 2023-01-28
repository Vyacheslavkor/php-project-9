DROP TABLE IF EXISTS urls;

CREATE TABLE urls (
    id         integer PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name       varchar(255) NOT NULL,
    created_at TIMESTAMP(0) NOT NULL
);

DROP TABLE IF EXISTS url_checks;

CREATE TABLE url_checks (
    id          integer PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    url_id      integer,
    status_code smallint,
    h1          text,
    title       text,
    description text,
    created_at  TIMESTAMP(0) NOT NULL
);